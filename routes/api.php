<?php

use App\Http\Controllers\Api\V1\AdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/debug/olx/categories', function () {
    $response = Http::get('https://www.olx.com.lb/api/categories');

    return $response->json();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/ads', [AdController::class, 'createAd']);
    Route::get('/my-ads', [AdController::class, 'myAds']);
    Route::get('/ads/{id}', [AdController::class, 'singleAd']);
});
