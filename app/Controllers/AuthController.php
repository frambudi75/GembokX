<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\SessionService;
use App\Services\TotpService;
use App\Middleware\RateLimiter;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;
use App\Helpers\View;

class AuthController {
    public function showLogin(): void {
        AuthMiddleware::guestOnly();
        View::render('vault/login', [
            'title' => 'Masuk ke Brankas — GembokX'
        ], 'auth');
    }

    public function showRegister(): void {
        AuthMiddleware::guestOnly();
        // Pendaftaran akun publik dinonaktifkan: akun baru dikelola oleh Administrator
        Router::redirect('/login');
    }

    /**
     * API Pre-login: Mengambil Salt dan Iterasi KDF berdasarkan email pengguna
     * Ini langkah Zero-Knowledge krusial agar client dapat mendriver Master Key secara lokal.
     */
    public function prelogin(): void {
        if (!RateLimiter::checkIpRate('prelogin', 30, 60)) {
            Router::json(['success' => false, 'message' => 'Terlalu banyak permintaan prelogin dari IP Anda. Silakan tunggu 1 menit.'], 429);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $email = strtolower(trim($data['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Router::json(['success' => false, 'message' => 'Format email tidak valid.'], 400);
        }

        $user = User::findByEmail($email);
        if (!$user) {
            // Berikan salt dummy deterministik agar penyerang tidak bisa melakukan enumerasi email
            $dummySalt = hash('sha256', 'dummy_salt_' . $email);
            Router::json([
                'success' => true,
                'kdf_salt' => $dummySalt,
                'kdf_iterations' => 600000,
                'kdf_algorithm' => 'pbkdf2',
                'kdf_memory' => 65536,
                'kdf_parallelism' => 4,
                'totp_required' => false
            ]);
        }

        Router::json([
            'success' => true,
            'kdf_salt' => $user['kdf_salt'],
            'kdf_iterations' => (int) $user['kdf_iterations'],
            'kdf_algorithm' => $user['kdf_algorithm'] ?? 'pbkdf2',
            'kdf_memory' => (int) ($user['kdf_memory'] ?? 65536),
            'kdf_parallelism' => (int) ($user['kdf_parallelism'] ?? 4),
            'totp_required' => (bool) $user['totp_enabled']
        ]);
    }

    /**
     * Proses Login Utama
     */
    public function login(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $email = strtolower(trim($data['email'] ?? ''));
        $authKey = trim($data['auth_key'] ?? '');
        $totpCode = trim($data['totp_code'] ?? '');

        if (empty($email) || empty($authKey)) {
            Router::json(['success' => false, 'message' => 'Email dan Auth Key wajib diisi.'], 400);
        }

        // Cek Rate Limiter
        if (RateLimiter::isLocked($email)) {
            $lockedUser = User::findByEmail($email);
            if ($lockedUser) {
                ActivityLog::log($lockedUser['id'], 'account_locked');
            }
            Router::json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan gagal. Akun dikunci sementara selama 15 menit.'
            ], 429);
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($authKey, $user['auth_hash'])) {
            RateLimiter::recordFailure($email);
            if ($user) {
                ActivityLog::log($user['id'], 'login_failed');
            }
            Router::json(['success' => false, 'message' => 'Email atau Master Password salah.'], 401);
        }

        // Cek status aktif akun
        if (isset($user['is_active']) && (int) $user['is_active'] === 0) {
            Router::json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan oleh Administrator.'
            ], 403);
        }

        // Cek 2FA jika aktif
        if (!empty($user['totp_enabled'])) {
            if (empty($totpCode) || !TotpService::verifyCode($user['totp_secret'], $totpCode)) {
                RateLimiter::recordFailure($email);
                ActivityLog::log($user['id'], 'login_failed');
                Router::json([
                    'success' => false,
                    'totp_required' => true,
                    'message' => 'Kode autentikasi 2FA (TOTP) salah atau kadaluarsa.'
                ], 401);
            }
        }

        // Reset Rate Limiter saat berhasil
        RateLimiter::reset($email);

        // Inisialisasi Sesi Login
        SessionService::login($user);
        ActivityLog::log($user['id'], 'login_success');

        Router::json([
            'success' => true,
            'message' => 'Login berhasil.',
            'redirect' => Router::url('/vault'),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'] ?? 'user',
                'full_name' => $user['full_name'] ?? '',
                'avatar' => $user['avatar'] ?? '',
                'encrypted_vault_key' => $user['encrypted_vault_key'],
                'vault_key_iv' => $user['vault_key_iv']
            ]
        ]);
    }

    /**
     * Pendaftaran Akun Baru (Publik Dinonaktifkan)
     */
    public function register(): void {
        Router::json([
            'success' => false,
            'message' => 'Pendaftaran mandiri dinonaktifkan. Pembuatan akun baru hanya dapat dilakukan melalui Administrator.'
        ], 403);
    }

    public function logout(): void {
        $user = SessionService::user();
        if ($user) {
            ActivityLog::log($user['id'], 'logout');
        }
        SessionService::logout();
        Router::redirect('/login');
    }
}
