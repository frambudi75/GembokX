<?php
namespace App\Middleware;

use App\Config\Database;
use PDO;

class RateLimiter {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 Menit

    public static function isLocked(string $email): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT failed_login_attempts, locked_until FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return false;
            }

            if (!empty($user['locked_until'])) {
                $lockedUntil = strtotime($user['locked_until']);
                if ($lockedUntil > time()) {
                    return true;
                }
            }

            return false;
        } catch (\PDOException) {
            return false;
        }
    }

    public static function recordFailure(string $email): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, failed_login_attempts FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $attempts = $user['failed_login_attempts'] + 1;
                $lockedUntil = null;

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_SECONDS);
                }

                $update = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?");
                $update->execute([$attempts, $lockedUntil, $user['id']]);
            }
        } catch (\PDOException) {
            // Abaikan kegagalan logging rate limiter
        }
    }

    public static function reset(string $email): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = ?");
            $stmt->execute([$email]);
        } catch (\PDOException) {
            // Abaikan
        }
    }

    /**
     * Rate limiting berbasis IP menggunakan CacheService (Redis / File fallback)
     */
    public static function checkIpRate(string $action, int $maxAttempts = 30, int $decaySeconds = 60): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_' . $action . '_' . md5($ip);
        $current = (int) \App\Services\CacheService::get($key, 0);

        if ($current >= $maxAttempts) {
            return false;
        }

        \App\Services\CacheService::set($key, $current + 1, $decaySeconds);
        return true;
    }
}
