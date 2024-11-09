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

Route::get('{any}', function () {
    return view('app');
})->where("any", ".*");

Route::get('/dd', function () {
    $userId = "1628997899";
    $startDateTime = Carbon::now()->subYears(10);  // например, все события за последние 10 лет
    $endDateTime = Carbon::now()->addYears(10);
    $events = Event::get(
        $startDateTime, $endDateTime,
    );
    dd($events);
    $filteredEvents = $events->filter(function ($event) use ($userId) {
        return strpos($event->description, "Id: {$userId}") !== false;
    });

    // Если нет подходящих событий, возвращаем сообщение
    if ($filteredEvents->isEmpty()) {
        return response()->json(['message' => 'No events found for this user ID'], 404);
    }

    // Преобразуем события в массив для JSON-ответа
    $result = $filteredEvents->map(function ($event) {
        return [
            'id' => $event->id,
            'title' => $event->name,
            'description' => $event->description,
            'start' => $event->startDateTime->format('Y-m-d H:i:s'),
            'end' => $event->endDateTime->format('Y-m-d H:i:s'),
        ];
    });
    dd($result);
    return response()->json($result);
});
