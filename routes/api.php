<?php

use App\Http\Controllers\Api\CalendarServiceController;
use App\Http\Controllers\Api\CheckEventsController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\Telegram\MessageController;
use App\Http\Controllers\Api\Telegram\TelegramController;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/get-events', [EventsController::class, 'getEvents']);
Route::post('/check-events', [CheckEventsController::class, 'checkEvents']);
Route::post('/message', [MessageController::class, 'sendMessage']);
Route::post('/telegram/callback-query', [TelegramController::class, 'handleCallbackQuery']);

Route::post('/initialize-cache', [CalendarServiceController::class, 'initializeCache']);

