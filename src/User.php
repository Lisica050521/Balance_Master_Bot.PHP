<?php

namespace App;

use PDO;
use Dotenv\Dotenv;

class User
{
    private $db;

    public function __construct()
    {
        Dotenv::createImmutable(dirname(__DIR__))->load();

        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $this->db = new PDO(
            "pgsql:host=$host;port=$port;dbname=$dbname",
            $user,
            $password
        );
    }

    public function getByTelegramId($telegramId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE telegram_id = :telegram_id");
        $stmt->execute(['telegram_id' => $telegramId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($telegramId)
    {
        $stmt = $this->db->prepare("INSERT INTO users (telegram_id, balance) VALUES (:telegram_id, 0.00)");
        $stmt->execute(['telegram_id' => $telegramId]);
    }

    public function checkBalance($telegramId)
    {
        $user = $this->getByTelegramId($telegramId);
        return $user ? $user->balance : 0.00;
    }

    public function updateBalance($telegramId, $amount)
    {
        $balance = $this->checkBalance($telegramId);

        if ($balance + $amount < 0) {
            return false;
        }

        // Обновляем баланс
        $stmt = $this->db->prepare("UPDATE users SET balance = balance + :amount WHERE telegram_id = :telegram_id");
        $stmt->execute(['amount' => $amount, 'telegram_id' => $telegramId]);

        return true;
    }
}