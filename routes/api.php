<?php

use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\VotingApiController;
use App\Http\Controllers\Api\ResultsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Current user (always available)
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role');
    });

    // API Access - Gated by Premium plan (api_access feature)
    Route::middleware(['plan.feature:api_access'])->group(function () {

        // Events
        Route::prefix('events')->group(function () {
            Route::get('/', [EventApiController::class, 'index']);
            Route::get('/active', [EventApiController::class, 'active']);
            Route::get('/{event}', [EventApiController::class, 'show']);
            Route::get('/{event}/divisions', [EventApiController::class, 'divisions']);
            Route::get('/{event}/entries', [EventApiController::class, 'entries']);
            Route::get('/{event}/voting-config', [EventApiController::class, 'votingConfig']);
            Route::get('/{event}/modules', [EventApiController::class, 'modules']);
        });

        // Voting
        Route::prefix('voting')->group(function () {
            Route::post('/{event}/vote', [VotingApiController::class, 'castVote']);
            Route::get('/{event}/my-votes', [VotingApiController::class, 'myVotes']);
            Route::get('/{event}/has-voted', [VotingApiController::class, 'hasVoted']);
            Route::post('/{event}/validate', [VotingApiController::class, 'validateVote']);
        });

        // Results
        Route::prefix('results')->group(function () {
            Route::get('/{event}', [ResultsApiController::class, 'index']);
            Route::get('/{event}/division/{division}', [ResultsApiController::class, 'byDivision']);
            Route::get('/{event}/leaderboard', [ResultsApiController::class, 'leaderboard']);
            Route::get('/{event}/summary', [ResultsApiController::class, 'summary']);
        });

        // Admin endpoints
        Route::middleware(['role:Administrator'])->prefix('admin')->group(function () {
            Route::get('/users', [EventApiController::class, 'users']);
            Route::get('/templates', [EventApiController::class, 'templates']);
            Route::get('/voting-types', [EventApiController::class, 'votingTypes']);
        });
    });
});
