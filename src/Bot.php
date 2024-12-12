<?php

namespace App;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

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
        $offset = 0;  // Изначальный offset

        while (true) {
            // Получаем обновления с учетом offset
            $updates = $this->api->getUpdates($offset);

            foreach ($updates as $update) {
                $message = $update->getMessage();
                $telegramId = $message->getFrom()->getId();
                $text = $message->getText();

                // Проверка существования пользователя
                $user = $this->user->getByTelegramId($telegramId);

                // Если пользователя нет, создаем нового
                if (!$user) {
                    $this->user->create($telegramId);
                    $this->api->sendMessage($telegramId, "Ваш аккаунт был создан. Баланс: $0.00");
                } else {
                    // Не отправляем сообщение с балансом, если он уже есть
                    // Обрабатываем сообщение пользователя
                    $this->handleMessage($telegramId, $text);
                }

                // Обновляем offset для следующего запроса
                $offset = $update->getUpdateId() + 1;
            }

            // Пауза между запросами, чтобы не перегружать сервер Telegram
            sleep(1);
        }
    }

    private function handleMessage($telegramId, $text)
    {
        // Заменяем запятую на точку
        $text = str_replace(',', '.', $text);

        // Проверяем, является ли введенный текст числом
        if (is_numeric($text)) {
            $amount = (float)$text;  // Преобразуем строку в число с плавающей запятой
            $balance = $this->user->checkBalance($telegramId);

            if ($balance + $amount < 0) {
                $this->api->sendMessage($telegramId, "Ошибка: недостаточно средств на счете.");
            } else {
                $this->user->updateBalance($telegramId, $amount);
                $newBalance = $balance + $amount;
                $this->api->sendMessage($telegramId, "Ваш новый баланс: $newBalance");
            }
        } else {
            $this->api->sendMessage($telegramId, "Пожалуйста, отправьте число для пополнения или списания со счета.");
        }
    }
}