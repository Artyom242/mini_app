<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\GoogleCalendar\Event;

class EventsController extends Controller
{
    public function getEvents(Request $request)
    {
        $selectedDate = $request->input("date");
        $events = $this->getEventsForDate($selectedDate);

        $availableSlots = $this->getAvailableTimeSlots(collect($events), $selectedDate);

        if (empty($availableSlots)) {
            return response()->json(['message' => 'No upcoming events found.'], 404);
        }

        return response()->json($availableSlots);
    }

    private function getEventsForDate($date)
    {
        $events = Cache::get("calendar_events_{$date}");

        if (is_null($events)) {
            return [];
        }
        return $events;
    }

    private function getAvailableTimeSlots($events, $selectedDate)
    {
        $consultationSlots = [
            '08:45' => true,
        ];

        $receptionSlots = [
            '09:00' => true,
            '10:00' => true,
            '11:00' => true,
            '12:00' => true,
            '13:00' => true,
            '14:00' => true,
            '15:00' => true,
            '16:00' => true,
        ];

        $occupiedSlots = $events->filter(function ($event) use ($selectedDate) {
            return Carbon::parse($event->startDateTime)->format('Y-m-d') === $selectedDate;
        })->map(function ($event) {
            return [
                'start' => Carbon::parse($event->startDateTime)->format('H:i'),
                'end' => Carbon::parse($event->endDateTime)->format('H:i'),
            ];
        });

        // Проверка занятости консультационных слотов
        foreach ($occupiedSlots as $occupied) {
            foreach ($consultationSlots as $slot => $isAvailable) {
                if ($this->timeOverlaps($slot, Carbon::createFromFormat('H:i', $slot)->addMinutes(15)->format('H:i'), $occupied['start'], $occupied['end'])) {
                    $consultationSlots[$slot] = false; // Устанавливаем занятость
                }
            }
        }

        // Проверка занятости слотов приема
        foreach ($occupiedSlots as $occupied) {
            foreach ($receptionSlots as $time => $isAvailable) {
                $slotStart = $time;
                $slotEnd = Carbon::createFromFormat('H:i', $slotStart)->addMinutes(60)->format('H:i'); // Прием длится 1 час

                if ($this->timeOverlaps($slotStart, $slotEnd, $occupied['start'], $occupied['end'])) {
                    $receptionSlots[$time] = false; // Устанавливаем занятость
                }
            }
        }

        return [
            'consultation' => $consultationSlots,
            'reception' => $receptionSlots,
        ];
    }

    private function timeOverlaps($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2 && $start2 < $end1);
    }
}

