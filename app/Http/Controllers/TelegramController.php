<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Services\TelegramWebhookService;
    use App\Services\UserService;

    class TelegramController extends Controller
    {
        protected $telegramService;
        protected $userService;

        public function __construct(TelegramWebhookService $telegramService, UserService $userService)
        {
            $this->telegramService = $telegramService;
            $this->userService = $userService;
        }

        public function webhook(Request $request)
        {
            $update = $request->all();

            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $name = $update['message']['chat']['first_name'] ?? 'користувач';
                $text = $update['message']['text'];

                $user = $this->userService->getUserByChatId($chatId);

                if ($text == "/start") {
                    if ($user) {
                        $this->telegramService->sendMessage($chatId, "Привіт, $name! Раді бачити вас знову.");
                    } else {
                        $this->userService->addOrUpdateUser($chatId, $name);
                        $this->telegramService->sendMessage($chatId, "Вітаємо, $name! Ви додані до бази.");
                    }
                }
            }

            return response()->json(['status' => 'ok']);
        }
    }
