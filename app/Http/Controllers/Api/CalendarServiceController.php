<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\GoogleCalendar\Event;

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
}
