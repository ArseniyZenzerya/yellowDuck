<?php

    namespace App\Services;

    use App\Contracts\WebhookHandlerInterface;
    use App\Contracts\UserServiceInterface;

    class TelegramWebhookService implements WebhookHandlerInterface
    {
        protected TelegramService $telegramService;
        protected UserServiceInterface $userService;

        public function __construct(TelegramService $telegramService, UserServiceInterface $userService)
        {
            $this->telegramService = $telegramService;
            $this->userService = $userService;
        }

        public function handle(array $data): void
        {
            if (!isset($data['message'])) {
                return;
            }

            $chatId = $data['message']['chat']['id'];
            $name = $data['message']['chat']['first_name'] ?? 'користувач';
            $text = $data['message']['text'];

            if ($text === "/start") {
                $user = $this->userService->getUserByChatId($chatId);

                if ($user) {
                    $this->telegramService->sendMessage($chatId, "Привіт, $name! Раді бачити вас знову.");
                } else {
                    $this->userService->addOrUpdateUser($chatId, $name);
                    $this->telegramService->sendMessage($chatId, "Вітаємо, $name! Ви додані до бази.");
                }
            }
        }
    }
