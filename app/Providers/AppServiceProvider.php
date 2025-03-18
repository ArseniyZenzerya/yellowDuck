<?php

namespace App\Providers;

use App\Console\Commands\CreateTrelloBoard;
use App\Services\TelegramWebhookService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramWebhookService::class, function ($app) {
            return new TelegramWebhookService();
        });

        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });

        $this->app->singleton(CreateTrelloBoard::class, function ($app) {
            return new CreateTrelloBoard(
                env('TRELLO_API_KEY'),
                env('TRELLO_ACCESS_TOKEN')
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
