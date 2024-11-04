<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;

class CalendarServiceController extends Controller
{
    public function initializeCache()
    {
        $events = Event::get();
        $eventsByDate = [];

        foreach ($events as $event) {
            $date = $event->startDateTime->format('Y-m-d');

            if (!isset($eventsByDate[$date])) {
                $eventsByDate[$date] = [];
            }
            $eventsByDate[$date][] = [
                'startDateTime' => $event->startDateTime->format('H:i'),
                'endDateTime' => $event->endDateTime->format('H:i'),
            ];
        }

        return response()->json($eventsByDate);
    }

    public function getEventsForDate($date)
    {
        return Cache::remember("calendar_events_{$date}", now()->addDay(), function () use ($date) {
            // Если события не найдены в кэше, получаем их из Google Calendar
            $events = Event::get(Carbon::parse($date)->startOfDay(), Carbon::parse($date)->endOfDay());
            return $events;
        });
    }

    public function updateCacheForDate($date)
    {
        $events = Event::get(Carbon::parse($date)->startOfDay(), Carbon::parse($date)->endOfDay());
        Cache::put("calendar_events_{$date}", $events, now()->addDay());
    }


    public function createEvent($data)
    {
        $event = Event::create([
            'name' => $data['name'],
            'startDateTime' => $data['startDateTime'],
            'endDateTime' => $data['endDateTime'],
        ]);

        // Обновляем кэш для указанного дня
        $date = $data['startDateTime']->format('Y-m-d');
        $this->updateCacheForDate($date);

        return $event;
    }
}
