<?php

namespace App\Http\Controllers;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Models\Record;
use Illuminate\Contracts\View\View;
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

    /** Public catalog: list records, with optional search, filters, and sort. */
    public function index(Request $request): View
    {
        // The search box is scalar; ignore array-shaped input (e.g. ?search[]=x)
        // so it can't trigger an Array-to-string error on this public endpoint.
        $term = $request->query('search');
        $search = is_string($term) ? trim($term) : '';

        // Filter option lists are derived from the catalog itself, so the UI only
        // ever offers values that actually match something.
        $genres = Record::query()
            ->whereNotNull('genre')
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
        $genre = is_string($rawGenre) && $genres->contains($rawGenre) ? $rawGenre : null;

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
            ->when($search !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
            ))
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

    /** Public record detail. */
    public function show(Record $record): View
    {
        return view('records.show', compact('record'));
    }
}
