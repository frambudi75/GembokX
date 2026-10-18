<?php
namespace App\Services;

use App\Config\App;

class SessionService {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        App::boot();

        $lifetime = (int) App::env('SESSION_LIFETIME', 120) * 60;
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');

        session_start();

        // Inactivity timeout check (default 120 menit)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }

    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }

    public static function logout(): void {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function isLoggedIn(): bool {
        self::start();
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::start();
        if (!self::isLoggedIn()) {
            return null;
        }

        $userId = (int) $_SESSION['user_id'];
        $user = CacheService::remember("user_profile_{$userId}", 60, function() use ($userId) {
            return \App\Models\User::findById($userId);
        });

        if ($user) {
            return $user;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'] ?? '',
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['role'] ?? 'user',
        ];
    }

    public static function clearUserCache(?int $userId = null): void {
        $id = $userId ?? self::userId();
        if ($id) {
            CacheService::forget("user_profile_{$id}");
        }
    }

    public static function userId(): ?int {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function flash(string $key, string $message): void {
        self::start();
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string {
        self::start();
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
}
