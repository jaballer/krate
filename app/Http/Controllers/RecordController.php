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
        $search = trim((string) $request->query('search', ''));

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
