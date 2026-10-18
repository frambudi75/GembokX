<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Folder;
use App\Services\SessionService;
use App\Middleware\AuthMiddleware;
use App\Helpers\View;

class VaultController {
    public function index(): void {
        AuthMiddleware::check();
        $user = User::findById(SessionService::userId());

        View::render('vault/index', [
            'title' => 'Brankas Saya — GembokX',
            'user' => $user,
            'vaultKey' => [
                'encrypted' => $user['encrypted_vault_key'],
                'iv' => $user['vault_key_iv'],
                'salt' => $user['kdf_salt'],
                'iterations' => (int) $user['kdf_iterations'],
                'algorithm' => $user['kdf_algorithm'] ?? 'pbkdf2',
                'memory' => (int) ($user['kdf_memory'] ?? 65536),
                'parallelism' => (int) ($user['kdf_parallelism'] ?? 4)
            ],
            'autolockMinutes' => (int) ($user['autolock_minutes'] ?? 15)
        ], 'main');
    }

    public function audit(): void {
        AuthMiddleware::check();
        $user = User::findById(SessionService::userId());

        View::render('vault/audit', [
            'title' => 'Audit Keamanan Sandi — GembokX',
            'user' => $user,
            'vaultKey' => [
                'encrypted' => $user['encrypted_vault_key'],
                'iv' => $user['vault_key_iv'],
                'salt' => $user['kdf_salt'],
                'iterations' => (int) $user['kdf_iterations'],
                'algorithm' => $user['kdf_algorithm'] ?? 'pbkdf2',
                'memory' => (int) ($user['kdf_memory'] ?? 65536),
                'parallelism' => (int) ($user['kdf_parallelism'] ?? 4)
            ]
        ], 'main');
    }

    public function settings(): void {
        AuthMiddleware::check();
        $user = User::findById(SessionService::userId());
        $folders = Folder::getAllByUser($user['id']);

        View::render('vault/settings', [
            'title' => 'Pengaturan Akun & Keamanan — GembokX',
            'user' => $user,
            'folders' => $folders,
            'vaultKey' => [
                'encrypted' => $user['encrypted_vault_key'],
                'iv' => $user['vault_key_iv'],
                'salt' => $user['kdf_salt'],
                'iterations' => (int) $user['kdf_iterations'],
                'algorithm' => $user['kdf_algorithm'] ?? 'pbkdf2',
                'memory' => (int) ($user['kdf_memory'] ?? 65536),
                'parallelism' => (int) ($user['kdf_parallelism'] ?? 4)
            ],
            'autolockMinutes' => (int) ($user['autolock_minutes'] ?? 15),
            'discordWebhook' => $user['discord_webhook_url'] ?? ''
        ], 'main');
    }

    public function emergencyKit(): void {
        AuthMiddleware::check();
        $user = User::findById((int) SessionService::userId());

        View::render('vault/emergency_kit', [
            'title' => 'Lembar Pemulihan Darurat (Emergency Kit) — GembokX',
            'user' => $user
        ], null);
    }
}
