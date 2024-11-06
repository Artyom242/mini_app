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

Route::get('/dd', function () {
    $date = "08.11.2024";
    $times = ['08:45', '10:00', '11:00'];

    $occupiedTimes = [];

    try {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Получаем события Google Календаря за указанный день
        $events = Event::get($startOfDay, $endOfDay);
        foreach ($times as $time) {
            $startTime = Carbon::parse("{$time}");
            $endTime = $time === '08:45'
                ? $startTime->copy()->addMinutes(15)
                : $startTime->copy()->addHour();

            $startTimeFormatted = $startTime->format('H:i');
            $endTimeFormatted = $endTime->format('H:i');

            $isOccupied = false;

            foreach ($events as $event) {
                $eventStart = $event->startDateTime->format('H:i');
                $eventEnd = $event->endDateTime->format('H:i');

                if ($startTimeFormatted < $eventEnd && $eventStart < $endTimeFormatted) {
                    $isOccupied = true;
                    break;
                }

            }

            if ($isOccupied) {
                $occupiedTimes[] = $time;
            }
        }

        return response()->json([
            'date' => $date,
            'occupiedTimes' => $occupiedTimes,
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Произошла ошибка при проверке занятости: ' . $e->getMessage()], 500);
    }
});


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
