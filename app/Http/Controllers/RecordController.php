<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    /** Public catalog: list records, optionally filtered by a search term. */
    public function index(Request $request): View
    {
        // The search box is scalar; ignore array-shaped input (e.g. ?search[]=x)
        // so it can't trigger an Array-to-string error on this public endpoint.
        $term = $request->query('search');
        $search = is_string($term) ? trim($term) : '';

        $records = Record::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
            ))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('records.index', compact('records', 'search'));
    }

    /** Public record detail. */
    public function show(Record $record): View
    {
        return view('records.show', compact('record'));
    }
}
