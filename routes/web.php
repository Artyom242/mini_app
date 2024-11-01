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

//Route::get('/dd', function () {
//    $str = "2024-10-31";
//    $now = Carbon::now();
//    $events = Event::get();
//
//    $selectedCarbonDate = Carbon::parse($str)->format('Y-m-d');
//
//    $filteredEvents = $events->filter(function ($event) use ($selectedCarbonDate) {
//        $eventStartDate = Carbon::parse($event->startDateTime)->format('Y-m-d');
//        $eventEndDate = Carbon::parse($event->endDateTime)->format('Y-m-d');
//        return $eventStartDate == $selectedCarbonDate && $eventEndDate == $selectedCarbonDate;
//    });
//    if ($filteredEvents->isEmpty()) {
//        return response()->json(['message' => 'No upcoming events found.'], 404);
//    }
//
//    $formattedEvents = $filteredEvents->map(function ($event) {
//        return [
//            'id' => $event->id,
//            'summary' => $event->summary,
//            'start' => Carbon::parse($event->startDateTime)->format('Y-m-d H:i:s'),
//            'end' => Carbon::parse($event->endDateTime)->format('Y-m-d H:i:s'),
//        ];
//    });
//
//    dd(response()->json($formattedEvents));
//});


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
