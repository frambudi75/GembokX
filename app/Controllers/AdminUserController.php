<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\SessionService;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;
use App\Helpers\View;

class AdminUserController {
    public function __construct() {
        AuthMiddleware::check();
        $user = SessionService::user();
        if (($user['role'] ?? 'user') !== 'admin') {
            if (Router::isApiRequest()) {
                Router::json(['success' => false, 'message' => 'Akses ditolak: Hanya Administrator yang berwenang.'], 403);
            } else {
                Router::redirect('/vault');
            }
        }
    }

    /**
     * Tampilan Halaman Manajemen Pengguna
     */
    public function index(): void {
        $user = User::findById((int) SessionService::userId());
        View::render('vault/users', [
            'title' => 'Manajemen Pengguna & Anggota — PassVault',
            'user' => $user,
            'vaultKey' => [
                'encrypted' => $user['encrypted_vault_key'] ?? '',
                'iv' => $user['vault_key_iv'] ?? '',
                'salt' => $user['kdf_salt'] ?? '',
                'iterations' => (int) ($user['kdf_iterations'] ?? 600000)
            ]
        ]);
    }

    /**
     * API: Mengambil Daftar Semua Pengguna beserta Statistik
     */
    public function listUsers(): void {
        $users = User::getAllWithStats();
        Router::json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * API: Mendaftarkan Pengguna / Teman Baru dengan Kunci Enkripsi Zero-Knowledge
     */
    public function createUser(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $email = strtolower(trim($data['email'] ?? ''));
        $username = trim($data['username'] ?? '');
        $role = in_array($data['role'] ?? '', ['admin', 'user']) ? $data['role'] : 'user';
        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        $authKey = trim($data['auth_key'] ?? '');
        $kdfSalt = trim($data['kdf_salt'] ?? '');
        $kdfIterations = (int) ($data['kdf_iterations'] ?? 600000);
        $encryptedVaultKey = trim($data['encrypted_vault_key'] ?? '');
        $vaultKeyIv = trim($data['vault_key_iv'] ?? '');
        $recoveryCode = trim($data['recovery_code'] ?? '');

        if (empty($email) || empty($username) || empty($authKey) || empty($encryptedVaultKey) || empty($vaultKeyIv)) {
            Router::json(['success' => false, 'message' => 'Email, Username, dan Kunci Enkripsi wajib terisi.'], 400);
        }

        if (User::findByEmail($email)) {
            Router::json(['success' => false, 'message' => 'Email ini sudah terdaftar di sistem.'], 409);
        }

        try {
            $newUserId = User::create([
                'email' => $email,
                'username' => $username,
                'role' => $role,
                'full_name' => $fullName,
                'phone' => $phone,
                'is_active' => 1,
                'auth_key' => $authKey,
                'kdf_salt' => $kdfSalt,
                'kdf_iterations' => $kdfIterations,
                'encrypted_vault_key' => $encryptedVaultKey,
                'vault_key_iv' => $vaultKeyIv,
                'recovery_code' => $recoveryCode
            ]);

            ActivityLog::log(SessionService::userId(), "created_user_{$newUserId}");

            Router::json([
                'success' => true,
                'message' => "Pengguna {$username} ({$email}) berhasil ditambahkan!",
                'user_id' => $newUserId
            ]);
        } catch (\Exception $e) {
            Router::json(['success' => false, 'message' => 'Gagal membuat pengguna: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Aktifkan / Nonaktifkan Pengguna
     */
    public function toggleStatus(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $targetId = (int) ($data['id'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1);

        if ($targetId <= 0) {
            Router::json(['success' => false, 'message' => 'ID Pengguna tidak valid.'], 400);
        }

        if ($targetId === SessionService::userId()) {
            Router::json(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'], 400);
        }

        User::toggleStatus($targetId, $isActive);
        SessionService::clearUserCache($targetId);
        ActivityLog::log(SessionService::userId(), "toggled_status_user_{$targetId}_to_{$isActive}");

        $msg = $isActive ? 'Akun pengguna berhasil diaktifkan.' : 'Akun pengguna berhasil dinonaktifkan.';
        Router::json(['success' => true, 'message' => $msg]);
    }

    /**
     * API: Mengubah Role Pengguna (Admin / User)
     */
    public function changeRole(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $targetId = (int) ($data['id'] ?? 0);
        $role = in_array($data['role'] ?? '', ['admin', 'user']) ? $data['role'] : 'user';

        if ($targetId <= 0) {
            Router::json(['success' => false, 'message' => 'ID Pengguna tidak valid.'], 400);
        }

        if ($targetId === SessionService::userId()) {
            Router::json(['success' => false, 'message' => 'Anda tidak dapat mengubah peran akun Anda sendiri.'], 400);
        }

        User::changeRole($targetId, $role);
        SessionService::clearUserCache($targetId);
        ActivityLog::log(SessionService::userId(), "changed_role_user_{$targetId}_to_{$role}");

        Router::json(['success' => true, 'message' => 'Peran pengguna berhasil diubah menjadi ' . ucfirst($role) . '.']);
    }

    /**
     * API: Menghapus Pengguna Secara Permanen
     */
    public function deleteUser(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $targetId = (int) ($data['id'] ?? 0);
        if ($targetId <= 0) {
            Router::json(['success' => false, 'message' => 'ID Pengguna tidak valid.'], 400);
        }

        if ($targetId === SessionService::userId()) {
            Router::json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 400);
        }

        User::delete($targetId);
        SessionService::clearUserCache($targetId);
        ActivityLog::log(SessionService::userId(), "deleted_user_{$targetId}");

        Router::json(['success' => true, 'message' => 'Pengguna dan seluruh data brankasnya berhasil dihapus.']);
    }
}
