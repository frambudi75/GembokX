<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class VaultItem {
    public static function getAllByUser(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, folder_id, item_type, is_favorite, encrypted_payload, iv, auth_tag, created_at, updated_at 
            FROM vault_items 
            WHERE user_id = ? 
            ORDER BY is_favorite DESC, updated_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $userId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, folder_id, item_type, is_favorite, encrypted_payload, iv, auth_tag, created_at, updated_at 
            FROM vault_items 
            WHERE id = ? AND user_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO vault_items (
                user_id, folder_id, item_type, is_favorite, encrypted_payload, iv, auth_tag
            ) VALUES (
                :user_id, :folder_id, :item_type, :is_favorite, :encrypted_payload, :iv, :auth_tag
            )
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'folder_id' => !empty($data['folder_id']) ? (int) $data['folder_id'] : null,
            'item_type' => in_array($data['item_type'], ['login', 'note', 'card', 'identity']) ? $data['item_type'] : 'login',
            'is_favorite' => !empty($data['is_favorite']) ? 1 : 0,
            'encrypted_payload' => $data['encrypted_payload'],
            'iv' => $data['iv'],
            'auth_tag' => $data['auth_tag']
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, int $userId, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE vault_items SET 
                folder_id = :folder_id,
                item_type = :item_type,
                is_favorite = :is_favorite,
                encrypted_payload = :encrypted_payload,
                iv = :iv,
                auth_tag = :auth_tag
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'folder_id' => !empty($data['folder_id']) ? (int) $data['folder_id'] : null,
            'item_type' => in_array($data['item_type'], ['login', 'note', 'card', 'identity']) ? $data['item_type'] : 'login',
            'is_favorite' => !empty($data['is_favorite']) ? 1 : 0,
            'encrypted_payload' => $data['encrypted_payload'],
            'iv' => $data['iv'],
            'auth_tag' => $data['auth_tag'],
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    public static function delete(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM vault_items WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public static function toggleFavorite(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE vault_items 
            SET is_favorite = IF(is_favorite = 1, 0, 1) 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $userId]);
    }
}
