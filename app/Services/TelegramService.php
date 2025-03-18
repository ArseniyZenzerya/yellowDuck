<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;

    class TelegramService
    {
        public function sendMessage($chatId, $text): void
        {
            Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text
            ]);
        }
    }
