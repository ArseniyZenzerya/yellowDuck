<?php

    namespace App\Services;

    use App\Contracts\UserServiceInterface;
    use App\Models\User;

    class UserService implements UserServiceInterface
    {
        public function addOrUpdateUser(int $chatId, string $name): void
        {
            User::updateOrCreate(
                ['telegram_id' => $chatId],
                ['name' => $name]
            );
        }

        public function getUserByChatId(int $chatId): ?User
        {
            return User::where('telegram_id', $chatId)->first();
        }

        public function linkTrelloAccount(int $chatId, string $email): void
        {
            $user = $this->getUserByChatId($chatId);

            if ($user) {
                $user->trello_email = $email;
                $user->save();
            }
        }

        public function getAllUsersInGroup(int $chatId): array
        {
            return User::where('chat_id', $chatId)->get()->toArray();
        }
    }
