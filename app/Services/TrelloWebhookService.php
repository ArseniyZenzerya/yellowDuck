<?php

    namespace App\Services;

    use App\Contracts\WebhookHandlerInterface;

    class TrelloWebhookService implements WebhookHandlerInterface
    {
        protected TelegramService $telegramService;

        public function __construct(TelegramService $telegramService)
        {
            $this->telegramService = $telegramService;
        }

        public function handle(array $data): void
        {
            if (!isset($data['action']['type']) || $data['action']['type'] !== 'updateCard') {
                return;
            }

            $card = $data['action']['data']['card'] ?? [];
            $listAfter = $data['action']['data']['listAfter']['name'] ?? null;
            $listBefore = $data['action']['data']['listBefore']['name'] ?? null;

            if ($listBefore && $listAfter) {
                $message = "Картка '{$card['name']}' переміщена з '{$listBefore}' в '{$listAfter}'";
                $this->telegramService->sendMessage(env('TELEGRAM_GROUP_ID'), $message);
            }
        }
    }
