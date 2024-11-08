<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;

class MessageController extends Controller
{
    public function sendMessage(Request $request){
        $chatId = $request->input('chat_id');
        $date = $request->input('date');
        $formattedDate = Carbon::parse($date)->format('d.m.Y');
        $times = implode(', ', $request->input('times'));
        $messageText = "{$formattedDate} Вы записаны на прием к {$times}. \nПожалуйста, подтвердите вашу запись.";

        $appointmentId = Str::uuid()->toString();
        Cache::put("appointment_{$appointmentId}", [
            'chat_id' => $chatId,
            'date' => $formattedDate,
            'times' => $times,
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ], now()->addMinutes(20));
        Log::info("Cached Appointment Data", ['data' => Cache::get("appointment_{$appointmentId}")]);

        try {
            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Отменить',
                            'callback_data' => "cancel_appointment:{$appointmentId}"
                        ],
                        [
                            'text' => 'Подтвердить',
                            'callback_data' => "confirm_appointment:{$appointmentId}",
                        ]

                    ]
                ]
            ];

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $messageText,
                'reply_markup' => json_encode($keyboard)
            ]);
            Log::info("success");
            return response()->json(['status' => 'success', 'message' => 'Сообщение отправлено.']);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки сообщения в Telegram', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Не удалось отправить сообщение.']);
        }
    }
}
