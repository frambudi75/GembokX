<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\SessionService;
use App\Services\TotpService;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;

class SettingsController {
    public function __construct() {
        AuthMiddleware::check();
    }

    /**
     * Mempersiapkan secret 2FA baru
     */
    public function setup2fa(): void {
        $user = User::findById(SessionService::userId());
        $secret = TotpService::generateSecret(16);
        $otpUrl = TotpService::getOtpAuthUrl($user['email'], $secret, 'GembokX');

        Router::json([
            'success' => true,
            'secret' => $secret,
            'otp_url' => $otpUrl
        ]);
    }

    /**
     * Mengonfirmasi kode TOTP dan mengaktifkan 2FA
     */
    public function confirm2fa(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $secret = trim($data['secret'] ?? '');
        $code = trim($data['code'] ?? '');

        if (empty($secret) || empty($code)) {
            Router::json(['success' => false, 'message' => 'Secret dan kode 6-digit wajib diisi.'], 400);
        }

        if (!TotpService::verifyCode($secret, $code)) {
            Router::json(['success' => false, 'message' => 'Kode TOTP salah atau telah kadaluarsa.'], 400);
        }

        $userId = SessionService::userId();
        User::enable2fa($userId, $secret);
        ActivityLog::log($userId, '2fa_enabled');

        Router::json(['success' => true, 'message' => 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!']);
    }

    /**
     * Menonaktifkan 2FA
     */
    public function disable2fa(): void {
        $userId = SessionService::userId();
        User::disable2fa($userId);
        ActivityLog::log($userId, '2fa_disabled');

        Router::json(['success' => true, 'message' => '2FA telah dinonaktifkan.']);
    }

    /**
     * Mengubah Master Password (Re-encrypt vault key di browser)
     */
    public function changeMasterKey(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $currentAuthKey = trim($data['current_auth_key'] ?? '');
        $newAuthKey = trim($data['new_auth_key'] ?? '');
        $newEncryptedVaultKey = trim($data['new_encrypted_vault_key'] ?? '');
        $newVaultKeyIv = trim($data['new_vault_key_iv'] ?? '');

        if (empty($currentAuthKey) || empty($newAuthKey) || empty($newEncryptedVaultKey) || empty($newVaultKeyIv)) {
            Router::json(['success' => false, 'message' => 'Kunci otentikasi lama dan baru wajib diisi lengkap.'], 400);
        }

        $user = User::findById($userId);
        if (!password_verify($currentAuthKey, $user['auth_hash'])) {
            Router::json(['success' => false, 'message' => 'Master Password saat ini tidak cocok.'], 401);
        }

        User::updateVaultKeys($userId, $newAuthKey, $newEncryptedVaultKey, $newVaultKeyIv);
        ActivityLog::log($userId, 'master_key_changed');

        Router::json(['success' => true, 'message' => 'Master Password berhasil diperbarui!']);
    }

    /**
     * Memperbarui Data Profil Pengguna (Nama Lengkap, Username, No HP)
     */
    public function updateProfile(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $username = trim($data['username'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($username)) {
            Router::json(['success' => false, 'message' => 'Username tidak boleh kosong.'], 400);
        }

        User::updateProfile($userId, [
            'username' => $username,
            'full_name' => $fullName,
            'phone' => $phone
        ]);

        SessionService::clearUserCache($userId);
        $updatedUser = User::findById($userId);
        SessionService::login($updatedUser);
        ActivityLog::log($userId, 'profile_updated');

        Router::json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user' => [
                'username' => $updatedUser['username'],
                'full_name' => $updatedUser['full_name'],
                'phone' => $updatedUser['phone'],
                'avatar' => $updatedUser['avatar']
            ]
        ]);
    }

    /**
     * Mengunggah Foto Profil Pengguna (Maksimal 10MB, Otomatis Terkompresi Super Ringan)
     */
    public function uploadAvatar(): void {
        $userId = SessionService::userId();
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Router::json(['success' => false, 'message' => 'Gagal mengunggah berkas foto.'], 400);
        }

        $file = $_FILES['avatar'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            Router::json(['success' => false, 'message' => 'Ukuran berkas maksimal 10MB.'], 400);
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedTypes[$mime])) {
            Router::json(['success' => false, 'message' => 'Format gambar harus berupa JPG, PNG, WEBP, atau GIF.'], 400);
        }

        $targetDir = dirname(__DIR__, 2) . '/public/uploads/avatars';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Hapus foto profil lama pengguna jika ada untuk menghemat ruang disk
        $oldAvatars = glob($targetDir . '/avatar_' . $userId . '_*.*');
        if ($oldAvatars) {
            foreach ($oldAvatars as $old) {
                if (is_file($old)) @unlink($old);
            }
        }

        $filename = 'avatar_' . $userId . '_' . bin2hex(random_bytes(6)) . '.webp';
        $targetPath = $targetDir . '/' . $filename;
        $compressed = false;

        // Kompresi Gambar menggunakan PHP GD jika tersedia
        if (extension_loaded('gd')) {
            $rawContent = file_get_contents($file['tmp_name']);
            $srcImg = @imagecreatefromstring($rawContent);

            if ($srcImg !== false) {
                $origW = imagesx($srcImg);
                $origH = imagesy($srcImg);
                $minSide = min($origW, $origH);
                $cropX = (int) (($origW - $minSide) / 2);
                $cropY = (int) (($origH - $minSide) / 2);

                $targetSize = 320; // Ukuran standar avatar retina tajam
                $dstImg = imagecreatetruecolor($targetSize, $targetSize);

                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                imagefilledrectangle($dstImg, 0, 0, $targetSize, $targetSize, $transparent);

                imagecopyresampled($dstImg, $srcImg, 0, 0, $cropX, $cropY, $targetSize, $targetSize, $minSide, $minSide);

                if (function_exists('imagewebp')) {
                    imagewebp($dstImg, $targetPath, 82);
                    $compressed = true;
                } elseif (function_exists('imagejpeg')) {
                    $filename = str_replace('.webp', '.jpg', $filename);
                    $targetPath = $targetDir . '/' . $filename;
                    imagejpeg($dstImg, $targetPath, 85);
                    $compressed = true;
                }

                imagedestroy($dstImg);
                imagedestroy($srcImg);
            }
        }

        // Fallback jika GD tidak memproses
        if (!$compressed) {
            $ext = $allowedTypes[$mime];
            $filename = 'avatar_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $targetPath = $targetDir . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                Router::json(['success' => false, 'message' => 'Gagal menyimpan berkas foto ke server.'], 500);
            }
        }

        $avatarUrl = '/uploads/avatars/' . $filename;
        User::updateAvatar($userId, $avatarUrl);

        SessionService::clearUserCache($userId);
        $updatedUser = User::findById($userId);
        SessionService::login($updatedUser);
        ActivityLog::log($userId, 'avatar_updated');

        $finalSizeKb = is_file($targetPath) ? round(filesize($targetPath) / 1024, 1) : 0;

        Router::json([
            'success' => true,
            'message' => "Foto profil berhasil diperbarui dan dikompresi menjadi {$finalSizeKb} KB!",
            'avatar_url' => Router::url($avatarUrl),
            'size_kb' => $finalSizeKb
        ]);
    }

    /**
     * Memperbarui Algoritma Derivasi Kunci (KDF) dan Re-enkripsi Kunci Brankas
     */
    public function updateKdf(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $currentAuthKey = trim($data['current_auth_key'] ?? '');
        $newAuthKey = trim($data['new_auth_key'] ?? '');
        $newEncryptedVaultKey = trim($data['new_encrypted_vault_key'] ?? '');
        $newVaultKeyIv = trim($data['new_vault_key_iv'] ?? '');
        $newAlgorithm = in_array($data['new_kdf_algorithm'] ?? '', ['pbkdf2', 'argon2id']) ? $data['new_kdf_algorithm'] : 'pbkdf2';
        $newIterations = (int) ($data['new_kdf_iterations'] ?? ($newAlgorithm === 'argon2id' ? 3 : 600000));
        $newMemory = (int) ($data['new_kdf_memory'] ?? 65536);
        $newParallelism = (int) ($data['new_kdf_parallelism'] ?? 4);
        $newSalt = trim($data['new_kdf_salt'] ?? '');

        if (empty($currentAuthKey) || empty($newAuthKey) || empty($newEncryptedVaultKey) || empty($newVaultKeyIv) || empty($newSalt)) {
            Router::json(['success' => false, 'message' => 'Parameter derivasi KDF tidak lengkap.'], 400);
        }

        $user = User::findById($userId);
        if (!password_verify($currentAuthKey, $user['auth_hash'])) {
            Router::json(['success' => false, 'message' => 'Master Password saat ini tidak sesuai.'], 401);
        }

        User::updateKdfConfig(
            $userId,
            $newAuthKey,
            $newEncryptedVaultKey,
            $newVaultKeyIv,
            $newAlgorithm,
            $newIterations,
            $newMemory,
            $newParallelism,
            $newSalt
        );

        SessionService::clearUserCache($userId);
        $updatedUser = User::findById($userId);
        SessionService::login($updatedUser);
        ActivityLog::log($userId, 'master_key_changed');

        Router::json([
            'success' => true,
            'message' => 'Algoritma derivasi kunci (KDF) berhasil diperbarui ke ' . strtoupper($newAlgorithm) . '!'
        ]);
    }

    /**
     * Memperbarui Webhook URL Discord Pengguna
     */
    public function updateDiscord(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $webhookUrl = trim($data['webhook_url'] ?? '');
        if (!empty($webhookUrl) && !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            Router::json(['success' => false, 'message' => 'Format Webhook URL Discord tidak valid.'], 400);
        }

        User::updateDiscordWebhook($userId, !empty($webhookUrl) ? $webhookUrl : null);
        SessionService::clearUserCache($userId);

        Router::json([
            'success' => true,
            'message' => !empty($webhookUrl) 
                ? 'Webhook Discord berhasil disimpan! Notifikasi keamanan real-time kini aktif.'
                : 'Webhook Discord telah dinonaktifkan.'
        ]);
    }

    /**
     * Menguji Pengiriman Webhook ke Discord
     */
    public function testDiscord(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $webhookUrl = trim($data['webhook_url'] ?? '');
        if (empty($webhookUrl)) {
            $user = User::findById($userId);
            $webhookUrl = trim($user['discord_webhook_url'] ?? '');
        }

        if (empty($webhookUrl) || !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            Router::json(['success' => false, 'message' => 'Masukkan Webhook URL Discord yang valid.'], 400);
        }

        $user = User::findById($userId);
        $ok = \App\Services\DiscordNotifier::send($webhookUrl, 'webhook_test', [
            'user_email' => $user['email'],
            'user_name' => $user['username']
        ]);

        if ($ok) {
            Router::json(['success' => true, 'message' => 'Notifikasi uji coba berhasil dikirim ke saluran Discord Anda!']);
        } else {
            Router::json(['success' => false, 'message' => 'Gagal mengirim ke Discord. Periksa izin saluran atau format URL Webhook.'], 500);
        }
    }

    /**
     * Memperbarui Durasi Auto-Lock Inaktivitas
     */
    public function updateAutoLock(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $minutes = max(0, min(1440, (int) ($data['minutes'] ?? 15)));
        User::updateAutoLock($userId, $minutes);
        SessionService::clearUserCache($userId);

        Router::json([
            'success' => true,
            'message' => $minutes > 0 
                ? "Brankas akan otomatis terkunci setelah {$minutes} menit inaktivitas."
                : "Auto-lock inaktivitas telah dinonaktifkan.",
            'minutes' => $minutes
        ]);
    }
}
