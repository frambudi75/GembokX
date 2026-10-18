<?php
namespace App\Config;

class App {
    private static array $env = [];
    private static bool $booted = false;

    public static function boot(): void {
        if (self::$booted) {
            return;
        }

        // Set Default Timezone
        date_default_timezone_set('Asia/Jakarta');

        // Load .env jika ada
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);
                    $val = trim($val, "\"'");
                    self::$env[$key] = $val;
                    if (!isset($_SERVER[$key])) {
                        $_SERVER[$key] = $val;
                    }
                }
            }
        }

        self::$booted = true;
    }

    public static function env(string $key, mixed $default = null): mixed {
        if (!self::$booted) {
            self::boot();
        }
        return self::$env[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function isDebug(): bool {
        return filter_var(self::env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);
    }
}
