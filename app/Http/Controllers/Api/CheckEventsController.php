<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;

class CheckEventsController extends Controller
{
    public function checkEvents(Request $request)
    {
        $date = $request->input('date');
        $times = $request->input('times', []);

        $occupiedTimes = [];

        try {
            $startOfDay = Carbon::parse($date)->startOfDay();
            $endOfDay = Carbon::parse($date)->endOfDay();

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

                    if ($this->timeOverlaps($startTimeFormatted, $endTimeFormatted, $eventStart, $eventEnd)) {
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
    }

    private function timeOverlaps($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2 && $start2 < $end1);
    }
}
