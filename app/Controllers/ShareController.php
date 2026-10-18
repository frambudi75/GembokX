<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\SessionService;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;
use App\Helpers\View;
use PDO;

class ShareController {

    /**
     * API: Membuat Tautan Rahasia Sekali Pakai (Burn-After-Reading)
     * Payload telah dienkripsi di peramban klien dengan kunci rahasia fragment URL.
     */
    public function createSecret(): void {
        AuthMiddleware::check();
        $userId = SessionService::userId();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $encryptedPayload = trim($data['encrypted_payload'] ?? '');
        $iv = trim($data['iv'] ?? '');
        $authTag = trim($data['auth_tag'] ?? 'gcm-embedded');
        $expiresHours = max(1, min(72, (int) ($data['expires_hours'] ?? 24)));

        if (empty($encryptedPayload) || empty($iv)) {
            Router::json(['success' => false, 'message' => 'Data terenkripsi dan IV wajib terisi.'], 400);
        }

        $linkToken = bin2hex(random_bytes(24));
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiresHours * 3600));

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO secret_links (user_id, link_token, encrypted_payload, iv, auth_tag, views_left, expires_at)
            VALUES (?, ?, ?, ?, ?, 1, ?)
        ");
        $stmt->execute([$userId, $linkToken, $encryptedPayload, $iv, $authTag, $expiresAt]);

        ActivityLog::log($userId, 'created_secret_link');

        Router::json([
            'success' => true,
            'token' => $linkToken,
            'message' => 'Tautan rahasia berhasil dibuat.'
        ]);
    }

    /**
     * Tampilan Halaman Penerima Tautan Rahasia
     */
    public function showSecretPage(string $token): void {
        View::render('vault/secret_view', [
            'title' => 'Pesan Rahasia Terenkripsi — GembokX',
            'token' => $token
        ], null);
    }

    /**
     * API: Mengambil Ciphertext Tautan Rahasia Sekaligus Memusnahkannya (Burn-After-Reading)
     */
    public function consumeSecret(string $token): void {
        $pdo = Database::getConnection();

        // Bersihkan tautan kadaluarsa terlebih dahulu
        $pdo->query("DELETE FROM secret_links WHERE expires_at < NOW() OR views_left <= 0");

        $stmt = $pdo->prepare("SELECT * FROM secret_links WHERE link_token = ? AND views_left > 0 LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            Router::json([
                'success' => false,
                'message' => 'Tautan rahasia ini sudah hangus (sudah dibuka sebelumnya) atau telah kadaluarsa.'
            ], 404);
        }

        // Musnahkan (burn) segera dari server setelah dibaca 1 kali
        $del = $pdo->prepare("DELETE FROM secret_links WHERE id = ?");
        $del->execute([$row['id']]);

        Router::json([
            'success' => true,
            'encrypted_payload' => $row['encrypted_payload'],
            'iv' => $row['iv'],
            'auth_tag' => $row['auth_tag']
        ]);
    }

    /**
     * API: Mengirim Kredensial Langsung ke Rekan Pengguna
     */
    public function shareWithUser(): void {
        AuthMiddleware::check();
        $senderId = SessionService::userId();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $recipientId = (int) ($data['recipient_id'] ?? 0);
        $encryptedPayload = trim($data['encrypted_payload'] ?? '');
        $iv = trim($data['iv'] ?? '');
        $authTag = trim($data['auth_tag'] ?? 'gcm-embedded');
        $itemType = trim($data['item_type'] ?? 'login');
        $note = trim($data['note'] ?? '');

        if ($recipientId <= 0 || empty($encryptedPayload) || empty($iv)) {
            Router::json(['success' => false, 'message' => 'Penerima dan data terenkripsi wajib dipilih.'], 400);
        }

        if ($recipientId === $senderId) {
            Router::json(['success' => false, 'message' => 'Anda tidak dapat membagikan item ke diri Anda sendiri.'], 400);
        }

        $recipient = User::findById($recipientId);
        if (!$recipient || !(int) $recipient['is_active']) {
            Router::json(['success' => false, 'message' => 'Akun rekan penerima tidak ditemukan atau tidak aktif.'], 404);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO shared_items (sender_id, recipient_id, item_type, encrypted_payload, iv, auth_tag, note)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$senderId, $recipientId, $itemType, $encryptedPayload, $iv, $authTag, $note]);

        ActivityLog::log($senderId, "shared_item_to_user_{$recipientId}");

        Router::json([
            'success' => true,
            'message' => "Item berhasil dibagikan ke @{$recipient['username']}!"
        ]);
    }

    /**
     * API: Mengambil Daftar Item yang Dibagikan ke Pengguna Ini
     */
    public function getSharedWithMe(): void {
        AuthMiddleware::check();
        $userId = SessionService::userId();

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, u.username AS sender_username, u.email AS sender_email, u.full_name AS sender_full_name, u.avatar AS sender_avatar
            FROM shared_items s
            JOIN users u ON u.id = s.sender_id
            WHERE s.recipient_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();

        Router::json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * API: Hapus Item yang Dibagikan
     */
    public function deleteSharedItem(): void {
        AuthMiddleware::check();
        $userId = SessionService::userId();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if ($id <= 0) {
            Router::json(['success' => false, 'message' => 'ID item tidak valid.'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM shared_items WHERE id = ? AND recipient_id = ?");
        $stmt->execute([$id, $userId]);

        Router::json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari daftar dibagikan.'
        ]);
    }
}
