<?php

class Env
{
    private static $vars = [];

    public static function load($path = __DIR__ . '/../.env')
    {
        if (!file_exists($path)) {
            throw new Exception("Env file not found at: " . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            self::$vars[$key] = $value;

            // also make available to getenv()
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get($key, $default = null)
    {
        return self::$vars[$key] ?? getenv($key) ?? $default;
    }
}

// Load environment variables from .env file
Env::load();

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}