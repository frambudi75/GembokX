<?php
namespace App\Config;

class Security {
    public static function applyHeaders(): void {
        if (headers_sent()) {
            return;
        }

        // Anti-clickjacking
        header("X-Frame-Options: DENY");
        // Anti-MIME sniffing
        header("X-Content-Type-Options: nosniff");
        // Referrer Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
        // Batasi akses sensor/hardware
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
        // HSTS jika HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }

        // Content Security Policy (dukung Tailwind CSS CDN, Service Worker, Manifest, Web Crypto)
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; "
             . "worker-src 'self'; "
             . "manifest-src 'self'; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com data:; "
             . "img-src 'self' data: https:; "
             . "connect-src 'self'; "
             . "frame-ancestors 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self';";
        
        header("Content-Security-Policy: " . $csp);
    }

    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function clean(?string $input): string {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
