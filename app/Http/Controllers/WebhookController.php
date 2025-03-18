<?php

    namespace App\Http\Controllers;

    use App\Contracts\WebhookHandlerInterface;
    use Illuminate\Http\Request;

    class WebhookController extends Controller
    {
        protected WebhookHandlerInterface $handler;

        public function __construct(WebhookHandlerInterface $handler)
        {
            $this->handler = $handler;
        }

        public function webhook(Request $request)
        {
            $this->handler->handle($request->all());
            return response()->json(['status' => 'ok']);
        }
    }
