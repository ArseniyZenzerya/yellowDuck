<?php

    use App\Http\Controllers\TelegramController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\WebhookController;
    use App\Services\TelegramWebhookService;
    use App\Services\TrelloWebhookService;
    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider and all of them will
    | be assigned to the "api" middleware group. Make something great!
    |
    */

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });


    Route::post('/webhook/telegram', function (Request $request, TelegramWebhookService $handler) {
        return (new WebhookController($handler))->webhook($request);
    });

    Route::post('/webhook/trello', function (Request $request, TrelloWebhookService $handler) {
        return (new WebhookController($handler))->webhook($request);
    });
