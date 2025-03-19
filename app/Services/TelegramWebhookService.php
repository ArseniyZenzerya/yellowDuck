<?php

    namespace App\Services;

    use App\Contracts\WebhookHandlerInterface;
    use App\Contracts\UserServiceInterface;

    class TelegramWebhookService implements WebhookHandlerInterface
    {
        protected TelegramService $telegramService;
        protected UserServiceInterface $userService;
        protected TrelloService $trelloService;

        public function __construct(
            TelegramService $telegramService,
            UserServiceInterface $userService,
            TrelloService $trelloService
        ) {
            $this->telegramService = $telegramService;
            $this->userService = $userService;
            $this->trelloService = $trelloService;
        }

        public function handle(array $data): void
        {
            if (!isset($data['message'])) {
                return;
            }

            $chatId = $data['message']['chat']['id'];
            $name = $data['message']['chat']['first_name'] ?? 'користувач';
            $text = trim($data['message']['text']);

            $command = strtok($text, ' ');
            $argument = trim(str_replace($command, '', $text));

            match ($command) {
                '/start' => $this->handleStartCommand($chatId, $name),
                '/linkTrelloAccount' => $this->handleLinkTrelloAccountCommand($chatId, $argument),
                default => null,
            };
        }

        private function handleStartCommand(int $chatId, string $name): void
        {
            $user = $this->userService->getUserByChatId($chatId);

            $message = $user
                ? "Привіт, $name! Раді бачити вас знову."
                : "Вітаємо, $name! Ви додані до бази.";

            if (!$user) {
                $this->userService->addOrUpdateUser($chatId, $name);
            }

            $this->telegramService->sendMessage($chatId, $message);
        }

        private function handleLinkTrelloAccountCommand(int $chatId, string $email): void
        {
            if (empty($email)) {
                $this->telegramService->sendMessage(
                    $chatId,
                    "Будь ласка, введіть ваш email, який ви використовуєте в Trello в форматі /linkTrelloAccount example@gmail.com."
                );
                return;
            }

            if ($this->trelloService->isEmailInBoard($email)) {
                $this->userService->linkTrelloAccount($chatId, $email);
                $message = "Ваш акаунт Trello успішно лінковано з email: $email.";
            } else {
                $message = "Не вдалося знайти акаунт Trello з таким email. Перевірте правильність.";
            }

            $this->telegramService->sendMessage($chatId, $message);
        }
    }
