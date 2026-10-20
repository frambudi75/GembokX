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
        // Anti-Flash/PDF cross domain policy abuse
        header("X-Permitted-Cross-Domain-Policies: none");
        // Referrer Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
        // Batasi akses sensor/hardware
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
        // HSTS jika HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }

        // Content Security Policy (dukung Tailwind CSS CDN, Service Worker, Manifest, Web Crypto, WebAssembly, HIBP)
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' 'unsafe-eval' https://cdn.tailwindcss.com; "
             . "worker-src 'self' blob:; "
             . "manifest-src 'self'; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com data:; "
             . "img-src 'self' data: https:; "
             . "connect-src 'self' https://api.pwnedpasswords.com https:; "
             . "frame-ancestors 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self';";
        
        header("Content-Security-Policy: " . $csp);
    }

    private static function getAppKey(): string {
        $key = App::env('APP_KEY');
        if (empty($key)) {
            $key = 'gembokx_default_server_secret_fallback_key_2026';
        }
        return hash('sha256', (string) $key, true);
    }

    /**
     * Enkripsi data sensitif server-side menggunakan AES-256-GCM
     */
    public static function encrypt(string $plaintext): string {
        $key = self::getAppKey();
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        return 'gcm:' . base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($ciphertext);
    }

    /**
     * Dekripsi data sensitif server-side
     */
    public static function decrypt(string $payload): ?string {
        if (!str_starts_with($payload, 'gcm:')) {
            return $payload; // Kembalikan nilai langsung jika data lama belum terenkripsi
        }
        $parts = explode(':', substr($payload, 4));
        if (count($parts) !== 3) {
            return null;
        }
        [$b64Iv, $b64Tag, $b64Cipher] = $parts;
        $iv = base64_decode($b64Iv);
        $tag = base64_decode($b64Tag);
        $ciphertext = base64_decode($b64Cipher);
        $key = self::getAppKey();
        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $decrypted !== false ? $decrypted : null;
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
