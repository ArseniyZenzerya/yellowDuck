<?php

namespace App\Providers;

use App\Console\Commands\CreateTrelloBoard;
use App\Contracts\UserServiceInterface;
use App\Services\TelegramService;
use App\Services\TelegramWebhookService;
use App\Services\TrelloService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->singleton(TelegramWebhookService::class, function ($app) {
            $telegramService = $app->make(TelegramService::class);
            $trelloService = $app->make(TrelloService::class);
            $userService = $app->make(UserServiceInterface::class);
            return new TelegramWebhookService($telegramService, $userService, $trelloService);
        });

        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });

        $this->app->singleton(CreateTrelloBoard::class, function ($app) {
            return new CreateTrelloBoard(
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
