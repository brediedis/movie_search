<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieSearchController;
use App\Http\Controllers\SearchHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', MovieSearchController::class)->name('search');

Route::get('/movies/{imdbId}', [MovieController::class, 'show'])
    ->where('imdbId', 'tt\d{7,10}')
    ->name('movies.show');

Route::delete('/history', [SearchHistoryController::class, 'destroy'])->name('history.clear');
