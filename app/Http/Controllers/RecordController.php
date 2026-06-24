<?php

namespace App\Http\Controllers;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Models\Record;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    /**
     * Accepted `?sort=` values mapped to the [column, direction] applied to the
     * query. Only these keys are honoured and the pairs are applied verbatim, so
     * raw query input never reaches orderBy().
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SORTS = [
        'newest' => ['created_at', 'desc'],
        'artist' => ['artist', 'asc'],
        'year' => ['release_year', 'desc'],
        'price' => ['purchase_price', 'desc'],
    ];

    /**
     * Columns the public search box matches. Backed by a FULLTEXT index on
     * MariaDB/MySQL (see the add-fulltext-index migration); LIKE on SQLite.
     *
     * @var list<string>
     */
    private const SEARCH_COLUMNS = ['title', 'artist', 'genre', 'label'];

    /**
     * Mirrors MariaDB's default innodb_ft_min_token_size. Tokens shorter than
     * this aren't indexed, so a required `+short*` term would match nothing —
     * they're dropped from the boolean query, and when that empties it,
     * applySearch() falls back to LIKE (which handles short substrings).
     */
    private const FULLTEXT_MIN_TOKEN_LENGTH = 3;

    /** Public catalog: list records, with optional search, filters, and sort. */
    public function index(Request $request): View
    {
        // The search box is scalar; ignore array-shaped input (e.g. ?search[]=x)
        // so it can't trigger an Array-to-string error on this public endpoint.
        $term = $request->query('search');
        $search = is_string($term) ? trim($term) : '';

        // Filter option lists are derived from the catalog itself, so the UI only
        // ever offers values that actually match something. Blank genres are
        // excluded so the empty string can never become a real filter value
        // (the default "All genres" option submits genre=).
        $genres = Record::query()
            ->whereNotNull('genre')
            ->where('genre', '!=', '')
            ->distinct()
            ->orderBy('genre')
            ->pluck('genre');

        $decades = Record::query()
            ->whereNotNull('release_year')
            ->pluck('release_year')
            ->map(fn ($year) => intdiv((int) $year, 10) * 10)
            ->unique()
            ->sortDesc()
            ->values();

        // Validate each filter against its allowed set; unrecognised or
        // array-shaped input is dropped (treated as "no filter") rather than
        // erroring. Enum filters go through tryFrom(); genre/decade are checked
        // against the catalog-derived lists above.
        $rawGenre = $request->query('genre');
        $genre = is_string($rawGenre) && $rawGenre !== '' && $genres->containsStrict($rawGenre) ? $rawGenre : null;

        $rawFormat = $request->query('format');
        $format = is_string($rawFormat) ? RecordFormat::tryFrom($rawFormat) : null;

        $rawCondition = $request->query('condition');
        $condition = is_string($rawCondition) ? RecordCondition::tryFrom($rawCondition) : null;

        $rawDecade = $request->query('decade');
        $decade = is_string($rawDecade) && ctype_digit($rawDecade) && $decades->contains((int) $rawDecade)
            ? (int) $rawDecade
            : null;

        // Whitelisted sort; unknown keys fall back to the default.
        $rawSort = $request->query('sort');
        $sort = is_string($rawSort) && isset(self::SORTS[$rawSort]) ? $rawSort : 'newest';
        [$sortColumn, $sortDirection] = self::SORTS[$sort];

        $records = Record::query()
            ->when($search !== '', fn ($query) => $this->applySearch($query, $search))
            ->when($genre !== null, fn ($query) => $query->where('genre', $genre))
            ->when($format !== null, fn ($query) => $query->where('format', $format->value))
            ->when($condition !== null, fn ($query) => $query->where('condition', $condition->value))
            ->when($decade !== null, fn ($query) => $query->whereBetween('release_year', [$decade, $decade + 9]))
            // Keep a stable tiebreaker so pagination order is deterministic when
            // the primary sort column ties (or is null).
            ->orderBy($sortColumn, $sortDirection)
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $filters = [
            'genre' => $genre,
            'format' => $format?->value,
            'condition' => $condition?->value,
            'decade' => $decade,
        ];

        return view('records.index', [
            'records' => $records,
            'search' => $search,
            'sort' => $sort,
            'filters' => $filters,
            'genres' => $genres,
            'decades' => $decades,
            'hasActiveFilters' => $search !== '' || $genre !== null || $format !== null
                || $condition !== null || $decade !== null,
        ]);
    }

    /**
     * Apply the catalog search across {@see self::SEARCH_COLUMNS}.
     *
     * MariaDB/MySQL use the FULLTEXT index via MATCH … AGAINST in boolean mode
     * with required, prefix-matched tokens; SQLite (tests) has no FULLTEXT and
     * falls back to a substring LIKE over the same columns. Both broaden the
     * original title/artist-only search to genre and label.
     *
     * @param  Builder<Record>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        if (in_array($query->getModel()->getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $boolean = $this->booleanFullTextTerms($search);

            if ($boolean !== '') {
                $query->whereFullText(self::SEARCH_COLUMNS, $boolean, ['mode' => 'boolean']);

                return;
            }
        }

        // SQLite, or a fulltext term that reduced to nothing usable: substring LIKE.
        $query->where(function ($q) use ($search) {
            foreach (self::SEARCH_COLUMNS as $i => $column) {
                $i === 0
                    ? $q->where($column, 'like', "%{$search}%")
                    : $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Reduce a raw search value to a safe BOOLEAN-mode fulltext expression.
     *
     * The value is split on non-word characters, so separators like the hyphen
     * in "Jay-Z" become token boundaries (matching how InnoDB tokenises the
     * index) rather than being deleted into a token that isn't indexed. Tokens
     * below the min indexable length are dropped (see FULLTEXT_MIN_TOKEN_LENGTH)
     * so "Jay-Z" searches as `+Jay*` rather than a `+Z*` that can never match.
     * Each surviving token is required and prefix-matched (`+token*`) so
     * multi-word searches like "Miles Davis" need every word, not any of them.
     * When nothing usable remains the result is '', so the caller falls back to
     * LIKE instead of sending a bare wildcard to the parser (a syntax error).
     */
    private function booleanFullTextTerms(string $search): string
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($tokens)
            ->filter(fn (string $word) => mb_strlen($word) >= self::FULLTEXT_MIN_TOKEN_LENGTH)
            ->map(fn (string $word) => '+'.$word.'*')
            ->implode(' ');
    }

    /** Public record detail. */
    public function show(Record $record): View
    {
        return view('records.show', compact('record'));
    }
}
