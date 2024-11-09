<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;

class EventsController extends Controller
{
    public function getEvents(Request $request)
    {
        $userId = $request->input("id_user");
        $startDateTime = Carbon::now()->subYears();
        $endDateTime = Carbon::now()->addYears();

        $events = Event::get(
            $startDateTime, $endDateTime,
        );

        $filteredEvents = $events->filter(function ($event) use ($userId) {
            return strpos($event->description, "Id: {$userId}") !== false;
        });

        if ($filteredEvents->isEmpty()) {
            return response()->json(['message' => 'No events found for this user ID'], 404);
        }

        $now = Carbon::now();

        $pastEvents = $filteredEvents->filter(fn($event) => $event->startDateTime < $now);
        $upcomingEvents = $filteredEvents->filter(fn($event) => $event->startDateTime >= $now);

        $result = [
            'past_events' => $pastEvents->map(fn($event) => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'start' => $event->startDateTime->format('Y-m-d H:i'),
                'end' => $event->endDateTime->format('Y-m-d H:i'),
            ]),
            'upcoming_events' => $upcomingEvents->map(fn($event) => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'start' => $event->startDateTime->format('Y-m-d H:i'),
                'end' => $event->endDateTime->format('Y-m-d H:i'),
            ])
        ];
        return response()->json($result);
    }
}

