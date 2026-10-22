<?php

namespace App\Http\Controllers;

use App\Services\History\SearchHistory;
use App\Services\Omdb\Data\SearchResult;
use App\Services\Omdb\Exceptions\OmdbException;
use App\Services\Omdb\OmdbClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovieSearchController extends Controller
{
    public function __invoke(Request $request, OmdbClient $omdb, SearchHistory $history): View
    {
        $validator = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(OmdbClient::TYPES)],
            'page' => ['nullable', 'integer', 'min:1', 'max:'.SearchResult::MAX_PAGE],
        ], [
            'q.max' => 'Nosaukums nedrīkst būt garāks par :max rakstzīmēm.',
            'q.string' => 'Lūdzu, ievadiet filmas nosaukumu.',
            'type.in' => 'Nederīgs meklēšanas veids.',
            'page.*' => 'Nederīgs lapas numurs.',
        ]);

        $query = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $type = $request->query('type');
        $result = null;
        $error = null;

        if ($validator->fails()) {
            $error = $validator->errors()->first();
        } elseif ($query !== '') {
            $history->record($query);

            try {
                $result = $omdb->search($query, (int) $request->query('page', 1), $type);
            } catch (OmdbException $e) {
                report($e);
                $error = $e->userMessage();
            }
        }

        return view('search', [
            'query' => $query,
            'type' => is_string($type) && in_array($type, OmdbClient::TYPES, true) ? $type : null,
            'result' => $result,
            'error' => $error,
            'history' => $history->recent(),
        ]);
    }
}
