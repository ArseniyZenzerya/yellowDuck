<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Services\TelegramService;
    use App\Services\UserService;

    class TelegramController extends Controller
    {
        protected $telegramService;
        protected $userService;

        public function __construct(TelegramService $telegramService, UserService $userService)
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

                if ($text == "/start") {
                    $this->userService->addOrUpdateUser($chatId, $name);
                    $this->telegramService->sendMessage($chatId, "Вітаємо, $name! Ви додані до бази.");
                }
            }

            return response()->json(['status' => 'ok']);
        }
    }
