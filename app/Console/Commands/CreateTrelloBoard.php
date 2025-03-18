<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Http;

    class CreateTrelloBoard extends Command
    {
        protected $signature = 'trello:add-lists {boardId?}';
        protected $description = 'Додати списки "InProgress" та "Done" до існуючої дошки';

        protected $apiKey;
        protected $accessToken;
        protected $boardId;

        public function __construct()
        {
            parent::__construct();
            $this->apiKey = env('TRELLO_API_KEY');
            $this->accessToken = env('TRELLO_ACCESS_TOKEN');
            $this->boardId = env('TRELLO_BOARD_ID');
        }

        public function handle()
        {
            $boardId = $this->argument('boardId') ?? $this->boardId;

            if (!$this->apiKey || !$this->accessToken) {
                $this->error("API ключ або токен доступу відсутні! Переконайтеся, що змінні середовища налаштовані.");
                return;
            }

            $this->createList($boardId, 'InProgress');
            $this->createList($boardId, 'Done');

            $this->info('Списки "InProgress" і "Done" додано до дошки.');
        }

        protected function createList(string $boardId, string $listName)
        {
            $response = Http::post("https://api.trello.com/1/lists", [
                'name' => $listName,
                'idBoard' => $boardId,
                'key' => $this->apiKey,
                'token' => $this->accessToken,
            ]);

            if ($response->failed()) {
                $responseData = $response->json();
                $errorMessage = $responseData['error'] ?? $response->body() ?? 'Невідома помилка';

                $this->error("Не вдалося створити список '$listName'. Причина: $errorMessage");
                return;
            }

            $this->info("Список '$listName' створено на дошці.");
        }
    }
