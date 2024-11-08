<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Spatie\GoogleCalendar\Event;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('app');
})->name('application');

Route::get('/create', function () {
    $event = new Event;
    $event->name = "TEST LARAVEL";
    $event->startDateTime = Carbon\Carbon::now();
    $event->endDateTime = Carbon\Carbon::now()->addHours();

    $event->save();
    $e = Event::get();
    dd($e);
});

// функ: все записи .. записи на дату .. создания записи ..
// фронт: главная
