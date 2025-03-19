<?php

    namespace App\Services;

    use App\Models\User;
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

        public function isEmailInBoard(string $username): bool
        {
            $members = $this->getBoardMembers();

            foreach ($members as $member) {
                if (isset($member['username']) && $member['username'] === $username) {
                    return true;
                }
            }

            return false;
        }

        public function getTasksForUser(User $user): ?array
        {
            $trelloAccount = $user->trello_email;

            if (!$trelloAccount) {
                return null;
            }
            $url = "https://api.trello.com/1/boards/" . env("TRELLO_BOARD_ID") . "/cards";
            $response = Http::get($url, [
                'key' => $this->apiKey,
                'token' => $this->token,
            ]);

            return $response->successful() ? $response->json() : null;
        }

        public function getBoardLists(): array
        {
            $url = "https://api.trello.com/1/boards/{$this->boardId}/lists?key={$this->apiKey}&token={$this->token}";

            $response = Http::get($url);

            if ($response->successful()) {
                $lists = $response->json();

                return array_map(fn($list) => [
                    'id' => $list['id'],
                    'name' => $list['name'],
                ], $lists);
            } else {
                return [];
            }
        }

        public function getUsernameByMemberId(string $memberId): ?string
        {
            $url = "https://api.trello.com/1/members/{$memberId}?key={$this->apiKey}&token={$this->token}";
            $response = Http::get($url);

            if ($response->successful()) {
                $memberData = $response->json();
                return $memberData['username'] ?? null;
            }

            return null;
        }

    }
