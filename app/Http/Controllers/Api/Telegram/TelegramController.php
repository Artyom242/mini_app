<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\Event;
use Telegram\Bot\Api;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api("5771654442:AAEsqtfqlrLPVaW7RG8nxVcDHg6uz9LVfAI");
//        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function handleCallbackQuery(Request $request)
    {
        $callbackQuery = $request->input('callback_query');

        if ($callbackQuery) {
            $userId = $callbackQuery['from']['id'];
            $callbackData = $callbackQuery['data'];
            Log::info('Telegram CallbackQuery', ["er" => $callbackData]);

            list($action, $appointmentId) = explode(':', $callbackData);
            $appointmentData = Cache::get("appointment_{$appointmentId}");
            Log::info("AppointmentData:", ["Appoin" => $appointmentData]);

            if (!$appointmentData) {
                Log::error("Данные записи не найдены в кэше.");
                return;
            }

            if ($action === 'confirm_appointment') {
                $this->createEvent($appointmentData);

                $this->telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Запись успешно подтверждена!',
                ]);

                $this->telegram->editMessageReplyMarkup([
                    'chat_id' => $userId,
                    'message_id' => $callbackQuery['message']['message_id'],
                    'reply_markup' => json_encode(['inline_keyboard' => []]),
                ]);
            }

            if ($action === 'cancel_appointment') {
                $this->telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Запись отменена.',
                ]);

                $this->telegram->editMessageReplyMarkup([
                    'chat_id' => $userId,
                    'message_id' => $callbackQuery['message']['message_id'],
                    'reply_markup' => json_encode(['inline_keyboard' => []]),
                ]);
            }

            Cache::forget("appointment_{$appointmentId}");
            $te = Cache::get("appointment_{$appointmentId}");
            Log::info("Cash", ["cash" => $te]);
        }
    }

    private function createEvent($appointmentData)
    {
        $userId = $appointmentData['chat_id'];
        $name = $appointmentData['name'];
        $phone = $appointmentData['phone'];
        $date = $appointmentData['date'];
        $times = (array)$appointmentData['times'];
        Log::info("ad", ["times" => $times]);
        foreach ($times as $time) {
            $startDateTime = Carbon::parse($date . " " . $time, 'Asia/Krasnoyarsk');

            $duration = ($time === '08:45') ? 15 : 60;

            $endDateTime = $startDateTime->copy()->addMinutes($duration);

            $event = new Event;
            $event->name = "{$name} — telegram запись";
            $event->description = "Телефон: {$phone} \nId: $userId";
            $event->startDateTime = $startDateTime;
            $event->endDateTime = $endDateTime;

            try {
                $event->save();
                Log::info("Событие создано: {$startDateTime}");
            } catch (\Exception $e) {
                Log::error("Ошибка при создании события: " . $e->getMessage());
            }
        }
    }
}
