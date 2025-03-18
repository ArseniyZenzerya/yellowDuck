<?php

    namespace App\Contracts;

    use App\Models\User;

    interface UserServiceInterface
    {
        public function addOrUpdateUser(int $chatId, string $name): void;

        public function getUserByChatId(int $chatId): ?User;
    }


