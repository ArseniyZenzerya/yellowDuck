<?php

    namespace App\Services;

    use App\Contracts\WebhookHandlerInterface;
    use App\Contracts\UserServiceInterface;
    use Illuminate\Support\Facades\Log;

    class TelegramWebhookService implements WebhookHandlerInterface
    {
        protected TelegramService $telegramService;
        protected UserServiceInterface $userService;
        protected TrelloService $trelloService;

        public function __construct(
            TelegramService $telegramService,
            UserServiceInterface $userService,
            TrelloService $trelloService
        ) {
            $this->telegramService = $telegramService;
            $this->userService = $userService;
            $this->trelloService = $trelloService;
        }

        public function handle(array $data): void
        {
            if (!isset($data['message']['text'])) {
                Log::info('Received non-text message:', $data);
                return;
            }

            $chatId = $data['message']['chat']['id'];
            $name = $data['message']['chat']['first_name'] ?? 'користувач';
            $text = trim($data['message']['text']);

            $command = strtok($text, ' ');
            $argument = trim(str_replace($command, '', $text));

            match ($command) {
                '/start' => $this->handleStartCommand($chatId, $name),
                '/linkTrelloAccount' => $this->handleLinkTrelloAccountCommand($chatId, $argument),
                '/report' => $this->handleTaskReportCommand($chatId),
                default => null,
            };
        }

        private function handleStartCommand(int $chatId, string $name): void
        {
            $user = $this->userService->getUserByChatId($chatId);

            $message = $user
                ? "Привіт, $name! Раді бачити вас знову."
                : "Вітаємо, $name! Ви додані до бази.";

            if (!$user) {
                $this->userService->addOrUpdateUser($chatId, $name);
            }

            $this->telegramService->sendMessage($chatId, $message);
        }

        private function handleLinkTrelloAccountCommand(int $chatId, string $username): void
        {
            if (empty($username)) {
                $this->telegramService->sendMessage(
                    $chatId,
                    "Будь ласка, введіть ваш username, який ви використовуєте в Trello в форматі /linkTrelloAccount username."
                );
                return;
            }

            if ($this->trelloService->isEmailInBoard($username)) {
                $this->userService->linkTrelloAccount($chatId, $username);
                $message = "Ваш акаунт Trello успішно лінковано з username: $username.";
            } else {
                $message = "Не вдалося знайти акаунт Trello з таким username. Перевірте правильність.";
            }

            $this->telegramService->sendMessage($chatId, $message);
        }


        private function handleTaskReportCommand(int $chatId): void
        {
            $users = $this->userService->getAllUsersInGroup();
            $report = "Звіт по завданням:\n";

            $lists = $this->trelloService->getBoardLists();
            $statusMapping = [];

            foreach ($lists as $list) {
                if (isset($list['id'], $list['name'])) {
                    $statusMapping[$list['id']] = $list['name'];
                }
            }

            $boardMembers = $this->trelloService->getBoardMembers();
            $usernameMapping = [];
            foreach ($boardMembers as $member) {
                $usernameMapping[$member['username']] = $member['id'];
            }

            foreach ($users as $user) {
                $tasks = $this->trelloService->getTasksForUser($user);

                if (empty($tasks)) {
                    $report .= "{$user->name} - акаунт Trello не підключено або немає задач.\n";
                    continue;
                }

                $report .= "{$user->name} - поточні завдання:\n";

                $userTasks = array_filter($tasks, function($task) use ($user, $usernameMapping) {
                    return in_array($usernameMapping[$user->trello_username] ?? null, $task['idMembers']);
                });

                if (empty($userTasks)) {
                    $report .= "- Немає задач для цього користувача.\n";
                    continue;
                }

                foreach ($userTasks as $task) {
                    if (!is_array($task)) {
                        Log::warning("Некорректный формат задачи", ['task' => $task]);
                        continue;
                    }

                    Log::info("Деталі задачі: ", $task);

                    $taskName = $task['name'] ?? '[Без назви]';
                    $taskStatus = $statusMapping[$task['idList'] ?? ''] ?? '[Невідомий статус]';
                    $taskDueDate = isset($task['due']) ? date("Y-m-d", strtotime($task['due'])) : '[Без дати]';
                    $taskLink = $task['shortUrl'] ?? '[Немає посилання]';

                    $report .= "- {$taskName}\n  Статус: {$taskStatus}\n  Дата завершення: {$taskDueDate}\n  Посилання: {$taskLink}\n\n";
                }
            }

            $this->telegramService->sendMessage($chatId, $report);
        }


    }
