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
     * Mirrors MariaDB's default innodb_ft_min_token_size: tokens shorter than
     * this aren't indexed. A search containing one defers to LIKE (see
     * fullTextQuery()) rather than requiring a `+short*` that can never match.
     */
    private const FULLTEXT_MIN_TOKEN_LENGTH = 3;

    /**
     * MariaDB/InnoDB's default fulltext stopword list
     * (information_schema.INNODB_FT_DEFAULT_STOPWORD). These aren't indexed, so
     * a search containing one defers to LIKE rather than requiring a `+the*`
     * token that matches nothing.
     *
     * @var list<string>
     */
    private const FULLTEXT_STOPWORDS = [
        'a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en',
        'for', 'from', 'how', 'i', 'in', 'is', 'it', 'la', 'of', 'on', 'or',
        'that', 'the', 'this', 'to', 'und', 'was', 'what', 'when', 'where',
        'who', 'will', 'with', 'www',
    ];

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
            $boolean = $this->fullTextQuery($search);

            if ($boolean !== null) {
                $query->whereFullText(self::SEARCH_COLUMNS, $boolean, ['mode' => 'boolean']);

                return;
            }
        }

        // SQLite, or a search the fulltext index can't represent faithfully:
        // keep the exact submitted text as a required substring across columns.
        $query->where(function ($q) use ($search) {
            foreach (self::SEARCH_COLUMNS as $i => $column) {
                $i === 0
                    ? $q->where($column, 'like', "%{$search}%")
                    : $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Build a BOOLEAN-mode fulltext expression for $search, or null when the
     * index can't represent it faithfully (so applySearch() uses LIKE instead).
     *
     * The value is split on non-word characters, so separators like the hyphen
     * in "Jay-Z" become token boundaries, matching how InnoDB tokenises the
     * index. Fulltext is only used when EVERY token is indexable — at least
     * FULLTEXT_MIN_TOKEN_LENGTH characters and not a stopword. MySQL silently
     * skips tokens it won't index (too short, or a stopword like "the"), which
     * would make a required `+token*` match nothing ("The Chronic") or weaken a
     * multi-word search to its remaining words ("U2 War" → only "War"). In
     * those cases null defers to LIKE, which matches the exact submitted text
     * like the SQLite test path. Surviving tokens are required and
     * prefix-matched, so "Miles Davis" needs both words while "Krau" still
     * prefix-matches "Krautrock".
     */
    private function fullTextQuery(string $search): ?string
    {
        // MySQL's fulltext parser treats "_" and "'" as word characters, so
        // "foo_bar" / "don't" are single indexed tokens — but the split below
        // breaks on them, producing tokens the index doesn't contain. Defer
        // such searches to LIKE rather than build a query that can't match.
        if (preg_match("/[_']/", $search) === 1) {
            return null;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return null;
        }

        foreach ($tokens as $token) {
            if (mb_strlen($token) < self::FULLTEXT_MIN_TOKEN_LENGTH
                || in_array(mb_strtolower($token), self::FULLTEXT_STOPWORDS, true)) {
                return null;
            }
        }

        return collect($tokens)
            ->map(fn (string $token) => '+'.$token.'*')
            ->implode(' ');
    }

    /** Public record detail. */
    public function show(Record $record): View
    {
        $record->load('tracks');

        return view('records.show', compact('record'));
    }
}
