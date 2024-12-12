<?php

namespace App;

use TelegramBot\Api\BotApi;

class Bot
{
    private BotApi $api;
    private User $user;

    public function __construct()
    {
        $token = Config::get('BOT_TOKEN');
        $this->api = new BotApi($token);
        $this->user = new User();
    }

    public function run()
    {
        $offset = 0;

        while (true) {
            $updates = $this->api->getUpdates($offset);

            foreach ($updates as $update) {
                $message = $update->getMessage();
                $telegramId = $message->getFrom()->getId();
                $text = $message->getText();

                $user = $this->user->getByTelegramId($telegramId);

                if (!$user) {
                    $this->user->create($telegramId);
                    $this->api->sendMessage($telegramId, "Ваш аккаунт был создан. Баланс: $0.00");
                    $offset = $update->getUpdateId() + 1;
                    continue;
                }

                $this->handleMessage($telegramId, $text);

                $offset = $update->getUpdateId() + 1;
            }

            sleep(1);
        }
    }

    private function handleMessage($telegramId, $text)
    {
        $text = str_replace(',', '.', $text);

        if (is_numeric($text)) {
            $amount = (float)$text;
            $balance = $this->user->checkBalance($telegramId);

            if ($balance + $amount < 0) {
                $this->api->sendMessage($telegramId, "Ошибка: недостаточно средств на счете.");
                return;
            }

            $this->user->updateBalance($telegramId, $amount);
            $newBalance = $balance + $amount;
            $this->api->sendMessage($telegramId, "Ваш новый баланс: $$newBalance");
            return;
        }

        $this->api->sendMessage($telegramId, "Пожалуйста, отправьте число для пополнения или списания со счета.");
    }
}