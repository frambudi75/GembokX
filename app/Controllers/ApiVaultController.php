<?php
namespace App\Controllers;

use App\Models\VaultItem;
use App\Models\Folder;
use App\Services\SessionService;
use App\Middleware\AuthMiddleware;
use App\Helpers\Router;

class ApiVaultController {
    public function __construct() {
        AuthMiddleware::check();
    }

    /**
     * Mengambil seluruh ciphertext brankas dan folder pengguna
     */
    public function sync(): void {
        $userId = SessionService::userId();
        $items = VaultItem::getAllByUser($userId);
        $folders = Folder::getAllByUser($userId);
        $trashCount = VaultItem::countTrashByUser($userId);

        Router::json([
            'success' => true,
            'items' => $items,
            'folders' => $folders,
            'trash_count' => $trashCount
        ]);
    }

    /**
     * Mengambil seluruh ciphertext item yang ada di Tempat Sampah
     */
    public function getTrash(): void {
        $userId = SessionService::userId();
        $items = VaultItem::getTrashByUser($userId);

        Router::json([
            'success' => true,
            'items' => $items,
            'count' => count($items)
        ]);
    }

    /**
     * Menyimpan atau memperbarui item brankas (ciphertext)
     */
    public function saveItem(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $id = !empty($data['id']) ? (int) $data['id'] : null;
        $folderId = !empty($data['folder_id']) ? (int) $data['folder_id'] : null;
        $itemType = trim($data['item_type'] ?? 'login');
        $isFavorite = !empty($data['is_favorite']) ? 1 : 0;
        $encryptedPayload = trim($data['encrypted_payload'] ?? '');
        $iv = trim($data['iv'] ?? '');
        $authTag = trim($data['auth_tag'] ?? '');

        if (empty($encryptedPayload) || empty($iv) || empty($authTag)) {
            Router::json(['success' => false, 'message' => 'Payload terenkripsi, IV, dan Auth Tag wajib disertakan.'], 400);
        }

        $payload = [
            'user_id' => $userId,
            'folder_id' => $folderId,
            'item_type' => $itemType,
            'is_favorite' => $isFavorite,
            'encrypted_payload' => $encryptedPayload,
            'iv' => $iv,
            'auth_tag' => $authTag
        ];

        try {
            if ($id) {
                // Pastikan item milik user
                $existing = VaultItem::getById($id, $userId);
                if (!$existing) {
                    Router::json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
                }
                VaultItem::update($id, $userId, $payload);
                $savedId = $id;
            } else {
                $savedId = VaultItem::create($payload);
            }

            $savedItem = VaultItem::getById($savedId, $userId);

            Router::json([
                'success' => true,
                'message' => 'Item berhasil disimpan ke brankas.',
                'item' => $savedItem
            ]);
        } catch (\Exception $e) {
            Router::json(['success' => false, 'message' => 'Gagal menyimpan item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete (Pindahkan item ke Tempat Sampah)
     */
    public function deleteItem(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Router::json(['success' => false, 'message' => 'ID item tidak valid.'], 400);
        }

        $deleted = VaultItem::delete($id, $userId);
        if ($deleted) {
            Router::json([
                'success' => true,
                'message' => 'Item dipindahkan ke Tempat Sampah.',
                'trash_count' => VaultItem::countTrashByUser($userId)
            ]);
        } else {
            Router::json(['success' => false, 'message' => 'Item tidak ditemukan atau gagal dipindahkan.'], 404);
        }
    }

    /**
     * Pulihkan item dari Tempat Sampah
     */
    public function restoreItem(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Router::json(['success' => false, 'message' => 'ID item tidak valid.'], 400);
        }

        $restored = VaultItem::restore($id, $userId);
        if ($restored) {
            Router::json([
                'success' => true,
                'message' => 'Item berhasil dipulihkan ke brankas.',
                'trash_count' => VaultItem::countTrashByUser($userId)
            ]);
        } else {
            Router::json(['success' => false, 'message' => 'Item tidak ditemukan di Tempat Sampah.'], 404);
        }
    }

    /**
     * Hapus item secara permanen
     */
    public function destroyItem(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Router::json(['success' => false, 'message' => 'ID item tidak valid.'], 400);
        }

        $destroyed = VaultItem::destroy($id, $userId);
        if ($destroyed) {
            Router::json([
                'success' => true,
                'message' => 'Item telah dihapus secara permanen.',
                'trash_count' => VaultItem::countTrashByUser($userId)
            ]);
        } else {
            Router::json(['success' => false, 'message' => 'Gagal menghapus item permanen.'], 404);
        }
    }

    /**
     * Kosongkan seluruh isi Tempat Sampah
     */
    public function emptyTrash(): void {
        $userId = SessionService::userId();
        $count = VaultItem::emptyTrash($userId);

        Router::json([
            'success' => true,
            'message' => "Tempat Sampah berhasil dikosongkan ({$count} item dihapus).",
            'count' => $count,
            'trash_count' => 0
        ]);
    }

    /**
     * Toggle status favorit item
     */
    public function toggleFavorite(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Router::json(['success' => false, 'message' => 'ID item tidak valid.'], 400);
        }

        VaultItem::toggleFavorite($id, $userId);
        Router::json(['success' => true, 'message' => 'Status favorit diperbarui.']);
    }

    /**
     * Menyimpan folder baru
     */
    public function saveFolder(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $encryptedName = trim($data['encrypted_name'] ?? '');
        $iv = trim($data['iv'] ?? '');

        if (empty($encryptedName) || empty($iv)) {
            Router::json(['success' => false, 'message' => 'Nama folder terenkripsi dan IV wajib ada.'], 400);
        }

        $folderId = Folder::create($userId, $encryptedName, $iv);
        Router::json([
            'success' => true,
            'message' => 'Folder berhasil dibuat.',
            'folder' => [
                'id' => $folderId,
                'encrypted_name' => $encryptedName,
                'iv' => $iv
            ]
        ]);
    }

    /**
     * Menghapus folder
     */
    public function deleteFolder(): void {
        $userId = SessionService::userId();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Router::json(['success' => false, 'message' => 'ID folder tidak valid.'], 400);
        }

        Folder::delete($id, $userId);
        Router::json(['success' => true, 'message' => 'Folder berhasil dihapus.']);
    }
}
