<?php

namespace App\Http\Controllers;

use App\Services\History\SearchHistory;
use Illuminate\Http\RedirectResponse;

class SearchHistoryController extends Controller
{
    public function destroy(SearchHistory $history): RedirectResponse
    {
        $history->clear();

        return redirect()->route('search')->with('status', 'Meklēšanas vēsture notīrīta.');
    }
}
