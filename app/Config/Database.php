<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        App::boot();

        $host = App::env('DB_HOST', '127.0.0.1');
        $port = App::env('DB_PORT', '3306');
        $dbName = App::env('DB_NAME', 'passvault_db');
        $user = App::env('DB_USER', 'root');
        $pass = App::env('DB_PASS', '');

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            // Coba koneksi langsung ke database target
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Jika database belum ada (error 1049 Unknown database), buat otomatis
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $initDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $initPdo = new PDO($initDsn, $user, $pass, $options);
                    $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $initPdo = null;

                    // Sambungkan kembali setelah database dibuat
                    self::$pdo = new PDO($dsn, $user, $pass, $options);
                } catch (PDOException $createEx) {
                    die("Gagal inisialisasi database: " . $createEx->getMessage());
                }
            } else {
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }

        // Lakukan migrasi skema otomatis jika tabel belum dibuat
        self::ensureSchema(self::$pdo);

        return self::$pdo;
    }

    private static function ensureSchema(PDO $pdo): void {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if (!$check) {
                $schemaFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $pdo->exec($sql);
                }
            } else {
                // Pastikan kolom baru (role, full_name, avatar, phone, is_active) ada di tabel users
                $columns = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('role', $columns)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user' AFTER `username`");
                }
                if (!in_array('full_name', $columns)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `full_name` VARCHAR(150) NULL AFTER `role`");
                }
                if (!in_array('avatar', $columns)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `full_name`");
                }
                if (!in_array('phone', $columns)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(30) NULL AFTER `avatar`");
                }
                if (!in_array('is_active', $columns)) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `phone`");
                }

                // Tetapkan admin default untuk user pertama
                $pdo->exec("UPDATE `users` SET `role` = 'admin' WHERE `id` = 1 AND (`role` IS NULL OR `role` = 'user')");

                // Migrasi tabel shared_items untuk sharing sandi antar rekan
                $pdo->exec("CREATE TABLE IF NOT EXISTS `shared_items` (
                    `id`                INT AUTO_INCREMENT PRIMARY KEY,
                    `sender_id`         INT NOT NULL,
                    `recipient_id`      INT NOT NULL,
                    `item_type`         ENUM('login', 'note', 'card', 'identity') NOT NULL DEFAULT 'login',
                    `encrypted_payload` LONGTEXT NOT NULL,
                    `iv`                VARCHAR(64) NOT NULL,
                    `auth_tag`          VARCHAR(64) NOT NULL DEFAULT 'gcm-embedded',
                    `note`              VARCHAR(255) NULL,
                    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT `fk_share_sender` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                    CONSTRAINT `fk_share_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                    INDEX `idx_share_recipient` (`recipient_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                // Migrasi tabel secret_links untuk burn-after-reading secret sharing
                $pdo->exec("CREATE TABLE IF NOT EXISTS `secret_links` (
                    `id`                INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id`           INT NOT NULL,
                    `link_token`        VARCHAR(64) NOT NULL UNIQUE,
                    `encrypted_payload` LONGTEXT NOT NULL,
                    `iv`                VARCHAR(64) NOT NULL,
                    `auth_tag`          VARCHAR(64) NOT NULL DEFAULT 'gcm-embedded',
                    `views_left`        INT NOT NULL DEFAULT 1,
                    `expires_at`        DATETIME NOT NULL,
                    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT `fk_secret_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                    INDEX `idx_secret_token` (`link_token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                // Pastikan performa optimal dengan indeks komposit & pencarian
                $performanceIndexes = [
                    ['users', 'idx_user_username', '(`username`)'],
                    ['users', 'idx_user_role_status', '(`role`, `is_active`)'],
                    ['users', 'idx_user_created_at', '(`created_at`)'],
                    ['vault_items', 'idx_vault_user_folder', '(`user_id`, `folder_id`)'],
                    ['vault_items', 'idx_vault_user_updated', '(`user_id`, `updated_at`)'],
                    ['vault_items', 'idx_vault_user_created', '(`user_id`, `created_at`)'],
                    ['folders', 'idx_folder_user_created', '(`user_id`, `created_at`)'],
                    ['activity_logs', 'idx_log_event', '(`event_type`, `created_at`)'],
                    ['shared_items', 'idx_share_recipient_created', '(`recipient_id`, `created_at`)'],
                    ['shared_items', 'idx_share_sender_created', '(`sender_id`, `created_at`)'],
                    ['secret_links', 'idx_secret_expiry', '(`expires_at`, `views_left`)'],
                    ['secret_links', 'idx_secret_user_created', '(`user_id`, `created_at`)'],
                ];
                foreach ($performanceIndexes as [$tbl, $idxName, $idxCols]) {
                    try {
                        $idxCheck = $pdo->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = '{$idxName}'")->fetch();
                        if (!$idxCheck) {
                            $pdo->exec("ALTER TABLE `{$tbl}` ADD INDEX `{$idxName}` {$idxCols}");
                        }
                    } catch (\Throwable $ex) {}
                }
            }
        } catch (PDOException $e) {
            // Abaikan jika migrasi telah selesai
        }
    }
}
