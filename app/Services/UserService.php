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
    }
