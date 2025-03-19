<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;

    class TrelloService
    {
        protected string $apiKey;
        protected string $token;
        protected string $boardId;

        public function __construct()
        {
            $this->apiKey = env('TRELLO_API_KEY');
            $this->token = env('TRELLO_ACCESS_TOKEN');
            $this->boardId = env('TRELLO_BOARD_ID');
        }

        public function getBoardMembers(): array
        {
            $url = "https://api.trello.com/1/boards/{$this->boardId}/members";
            $response = Http::get($url, [
                'key' => $this->apiKey,
                'token' => $this->token,
            ]);

            return $response->json();
        }

        public function isEmailInBoard(string $email): bool
        {
            $members = $this->getBoardMembers();

            foreach ($members as $member) {
                if (isset($member['email']) && $member['email'] === $email) {
                    return true;
                }
            }

            return false;
        }
    }
