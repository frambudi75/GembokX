<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Folder {
    public static function getAllByUser(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, encrypted_name, iv, created_at FROM folders WHERE user_id = ? ORDER BY id ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $encryptedName, string $iv): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO folders (user_id, encrypted_name, iv) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $encryptedName, $iv]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
