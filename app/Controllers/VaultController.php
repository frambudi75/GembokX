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
                'iterations' => (int) $user['kdf_iterations']
            ]
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
                'iterations' => (int) $user['kdf_iterations']
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
                'iterations' => (int) $user['kdf_iterations']
            ]
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
