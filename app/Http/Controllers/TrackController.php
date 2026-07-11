<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrackController extends Controller
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
        'title' => ['title', 'asc'],
        'artist' => ['artist', 'asc'],
        'bpm' => ['bpm', 'desc'],
    ];

    /**
     * Columns the public search box matches (substring LIKE — the tracks table
     * has no FULLTEXT index).
     *
     * @var list<string>
     */
    private const SEARCH_COLUMNS = ['title', 'artist', 'album'];

    /** Public track library: list tracks, with optional search and sort. */
    public function index(Request $request): View
    {
        // The search box is scalar; ignore array-shaped input (e.g. ?search[]=x)
        // so it can't trigger an Array-to-string error on this public endpoint.
        $term = $request->query('search');
        $search = is_string($term) ? trim($term) : '';

        // Whitelisted sort; unknown keys fall back to the default.
        $rawSort = $request->query('sort');
        $sort = is_string($rawSort) && isset(self::SORTS[$rawSort]) ? $rawSort : 'newest';
        [$sortColumn, $sortDirection] = self::SORTS[$sort];

        $tracks = Track::query()
            // Eager-load the record so displayAlbum() in the list view doesn't N+1.
            ->with('record')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    foreach (self::SEARCH_COLUMNS as $i => $column) {
                        $i === 0
                            ? $q->where($column, 'like', "%{$search}%")
                            : $q->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            // Stable tiebreaker so pagination order is deterministic when the
            // primary sort column ties (or is null).
            ->orderBy($sortColumn, $sortDirection)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tracks.index', [
            'tracks' => $tracks,
            'search' => $search,
            'sort' => $sort,
            'hasActiveFilters' => $search !== '',
        ]);
    }

    /** Public track detail. */
    public function show(Track $track): View
    {
        return view('tracks.show', compact('track'));
    }
}
