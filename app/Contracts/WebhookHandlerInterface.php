<?php

    namespace App\Contracts;

    interface WebhookHandlerInterface
    {
        public function handle(array $data): void;
    }

