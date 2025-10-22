<?php

require_once __DIR__ . '/../main.php';

class Env
{
    private static $vars = [];

    public static function load()
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // skip comments or empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split into key/value pair
            [$key, $value] = array_map('trim', explode('=', $line, 2));

            // ✅ Strip surrounding single or double quotes
            $value = preg_replace('/^["\'](.*)["\']$/', '$1', $value);

            // ✅ Store in static vars and PHP env
            self::$vars[$key] = $value;
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get($key, $default = null)
    {
        return self::$vars[$key] ?? $default;
    }
}

// Load environment variables
Env::load();

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}
