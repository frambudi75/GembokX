<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class User {
    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        if ($row && !empty($row['totp_secret'])) {
            $row['totp_secret'] = \App\Config\Security::decrypt($row['totp_secret']);
        }
        return $row ?: null;
    }

    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && !empty($row['totp_secret'])) {
            $row['totp_secret'] = \App\Config\Security::decrypt($row['totp_secret']);
        }
        return $row ?: null;
    }

    public static function getAllWithStats(): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT 
                u.id, u.email, u.username, u.role, u.full_name, u.avatar, u.phone, 
                u.is_active, u.totp_enabled, u.created_at, u.updated_at,
                COUNT(v.id) AS total_items
            FROM users u
            LEFT JOIN vault_items v ON v.user_id = u.id
            GROUP BY u.id
            ORDER BY u.id ASC
        ";
        return $pdo->query($sql)->fetchAll();
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (
                email, username, role, full_name, avatar, phone, is_active,
                auth_hash, kdf_salt, kdf_iterations, kdf_algorithm, kdf_memory, kdf_parallelism,
                encrypted_vault_key, vault_key_iv, recovery_code_hash
            ) VALUES (
                :email, :username, :role, :full_name, :avatar, :phone, :is_active,
                :auth_hash, :kdf_salt, :kdf_iterations, :kdf_algorithm, :kdf_memory, :kdf_parallelism,
                :encrypted_vault_key, :vault_key_iv, :recovery_code_hash
            )
        ");

        $stmt->execute([
            'email' => strtolower(trim($data['email'])),
            'username' => trim($data['username']),
            'role' => $data['role'] ?? 'user',
            'full_name' => !empty($data['full_name']) ? trim($data['full_name']) : null,
            'avatar' => !empty($data['avatar']) ? trim($data['avatar']) : null,
            'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'auth_hash' => password_hash($data['auth_key'], PASSWORD_BCRYPT, ['cost' => 12]),
            'kdf_salt' => $data['kdf_salt'],
            'kdf_iterations' => (int) ($data['kdf_iterations'] ?? 600000),
            'kdf_algorithm' => $data['kdf_algorithm'] ?? 'pbkdf2',
            'kdf_memory' => (int) ($data['kdf_memory'] ?? 65536),
            'kdf_parallelism' => (int) ($data['kdf_parallelism'] ?? 4),
            'encrypted_vault_key' => $data['encrypted_vault_key'],
            'vault_key_iv' => $data['vault_key_iv'],
            'recovery_code_hash' => password_hash($data['recovery_code'], PASSWORD_BCRYPT, ['cost' => 12])
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users SET 
                full_name = :full_name,
                username = :username,
                phone = :phone
            WHERE id = :id
        ");

        return $stmt->execute([
            'full_name' => !empty($data['full_name']) ? trim($data['full_name']) : null,
            'username' => trim($data['username']),
            'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            'id' => $id
        ]);
    }

    public static function updateAvatar(int $id, string $avatarUrl): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatarUrl, $id]);
    }

    public static function toggleStatus(int $id, int $isActive): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive, $id]);
    }

    public static function changeRole(int $id, string $role): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }

    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function updateVaultKeys(int $id, string $authKey, string $encryptedVaultKey, string $vaultKeyIv): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users SET 
                auth_hash = :auth_hash,
                encrypted_vault_key = :encrypted_vault_key,
                vault_key_iv = :vault_key_iv
            WHERE id = :id
        ");

        return $stmt->execute([
            'auth_hash' => password_hash($authKey, PASSWORD_BCRYPT, ['cost' => 12]),
            'encrypted_vault_key' => $encryptedVaultKey,
            'vault_key_iv' => $vaultKeyIv,
            'id' => $id
        ]);
    }

    public static function enable2fa(int $id, string $secret): bool {
        $pdo = Database::getConnection();
        $encryptedSecret = \App\Config\Security::encrypt($secret);
        $stmt = $pdo->prepare("UPDATE users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?");
        return $stmt->execute([$encryptedSecret, $id]);
    }

    public static function disable2fa(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET totp_secret = NULL, totp_enabled = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function updateKdfConfig(
        int $id,
        string $authKey,
        string $encryptedVaultKey,
        string $vaultKeyIv,
        string $kdfAlgorithm,
        int $kdfIterations,
        int $kdfMemory,
        int $kdfParallelism,
        string $kdfSalt
    ): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users SET 
                auth_hash = :auth_hash,
                encrypted_vault_key = :encrypted_vault_key,
                vault_key_iv = :vault_key_iv,
                kdf_algorithm = :kdf_algorithm,
                kdf_iterations = :kdf_iterations,
                kdf_memory = :kdf_memory,
                kdf_parallelism = :kdf_parallelism,
                kdf_salt = :kdf_salt
            WHERE id = :id
        ");

        return $stmt->execute([
            'auth_hash' => password_hash($authKey, PASSWORD_BCRYPT, ['cost' => 12]),
            'encrypted_vault_key' => $encryptedVaultKey,
            'vault_key_iv' => $vaultKeyIv,
            'kdf_algorithm' => $kdfAlgorithm,
            'kdf_iterations' => $kdfIterations,
            'kdf_memory' => $kdfMemory,
            'kdf_parallelism' => $kdfParallelism,
            'kdf_salt' => $kdfSalt,
            'id' => $id
        ]);
    }

    public static function updateDiscordWebhook(int $id, ?string $webhookUrl): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET discord_webhook_url = ? WHERE id = ?");
        return $stmt->execute([$webhookUrl, $id]);
    }

    public static function updateAutoLock(int $id, int $minutes): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET autolock_minutes = ? WHERE id = ?");
        return $stmt->execute([$minutes, $id]);
    }
}
