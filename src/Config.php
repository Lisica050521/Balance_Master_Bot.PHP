<?php

namespace App;

class Config
{
    public static function get($key)
    {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            throw new \Exception('.env file not found');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (strpos($line, '=') === false) {
                continue;
            }

            [$envKey, $value] = explode('=', $line, 2);
            if ($envKey === $key) {
                return $value;
            }
        }

        throw new \Exception("Key $key not found in .env");
    }
}