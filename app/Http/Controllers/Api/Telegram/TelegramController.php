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
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function handleWebhook(Request $request)
    {
        $message = $request->input('message');
        $callbackQuery = $request->input('callback_query');

        if (isset($message['text']) && $message['text'] === '/start') {
            $chatId = $message['chat']['id'];
            $username = $message['chat']['username'] ?? 'не указан';
            $phoneNumber = $message['contact']['phone_number'] ?? 'не указан';

            $userData = "ID: {$chatId}, Username: {$username}, Phone: {$phoneNumber}" . PHP_EOL;
            file_put_contents(storage_path('logs/user_data.log'), $userData, FILE_APPEND);

            $this->handleStart($message['chat']['id']);
            return;
        }

        if ($callbackQuery) {
            $this->handleCallbackQuery($callbackQuery);
        }
    }

    public function handleStart($chatId)
    {
        $text = "Нажмите на кнопку 'Открыть' в самом низу или под этим сообщением, чтобы запустить Mini App.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Открыть',
                        'web_app' => [
                            'url' => 'https://6d4e-188-162-6-104.ngrok-free.app'
                        ]
                    ]
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    public function handleCallbackQuery(array  $callbackQuery)
    {

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
