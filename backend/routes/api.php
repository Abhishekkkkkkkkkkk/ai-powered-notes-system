<?php

use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php and
| all of them will be assigned to the "api" middleware group.
|
*/

// Set throttle rate limit for security
Route::middleware('throttle:60,1')->group(function () {
    
    // AI Semantic Search API
    Route::post('/notes/search', [NoteController::class, 'semanticSearch']);

    // AI Note Summary API
    Route::post('/notes/{id}/summary', [NoteController::class, 'generateSummary']);

    // Core Note CRUD REST APIs
    Route::apiResource('notes', NoteController::class);

});

