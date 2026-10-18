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

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        $maxAttempts = 5;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
                break; // Berhasil terhubung
            } catch (PDOException $e) {
                $lastException = $e;

                // Jika database belum dibuat (error 1049 Unknown database), inisialisasi otomatis
                if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                    try {
                        $initDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                        $initPdo = new PDO($initDsn, $user, $pass, $options);
                        $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $initPdo = null;

                        self::$pdo = new PDO($dsn, $user, $pass, $options);
                        break;
                    } catch (\Throwable $createEx) {
                        // Teruskan ke retry berikutnya jika container DB masih booting
                    }
                }

                // Jika masih dalam batas percobaan dan DB sedang booting (misal di Docker)
                if ($attempt < $maxAttempts) {
                    sleep(1);
                }
            }
        }

        if (self::$pdo === null) {
            self::handleConnectionFailure($lastException);
        }

        // Lakukan verifikasi dan perbaikan skema database mandiri (Self-Healing)
        self::ensureSchema(self::$pdo);

        return self::$pdo;
    }

    /**
     * Memverifikasi dan memperbaiki seluruh tabel, relasi, dan kolom secara otomatis
     */
    public static function ensureSchema(PDO $pdo): void {
        try {
            $requiredTables = ['users', 'folders', 'vault_items', 'activity_logs', 'shared_items', 'secret_links'];
            $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $missingTables = array_diff($requiredTables, $existingTables);

            // Jika ada tabel inti yang belum terimpor, jalankan schema.sql per-statement
            if (!empty($missingTables)) {
                $schemaFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'schema.sql';
                if (file_exists($schemaFile)) {
                    self::executeSqlFile($pdo, $schemaFile);
                }
            }

            // Validasi ulang keberadaan tabel users sebelum memvalidasi kolom
            $usersExist = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if ($usersExist) {
                $columns = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
                
                $userColMigrations = [
                    'role' => "ALTER TABLE `users` ADD COLUMN `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user' AFTER `username`",
                    'full_name' => "ALTER TABLE `users` ADD COLUMN `full_name` VARCHAR(150) NULL AFTER `role`",
                    'avatar' => "ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `full_name`",
                    'phone' => "ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(30) NULL AFTER `avatar`",
                    'is_active' => "ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `phone`",
                    'kdf_algorithm' => "ALTER TABLE `users` ADD COLUMN `kdf_algorithm` ENUM('pbkdf2', 'argon2id') NOT NULL DEFAULT 'pbkdf2' AFTER `kdf_iterations`",
                    'kdf_memory' => "ALTER TABLE `users` ADD COLUMN `kdf_memory` INT NOT NULL DEFAULT 65536 AFTER `kdf_algorithm`",
                    'kdf_parallelism' => "ALTER TABLE `users` ADD COLUMN `kdf_parallelism` INT NOT NULL DEFAULT 4 AFTER `kdf_memory`",
                    'discord_webhook_url' => "ALTER TABLE `users` ADD COLUMN `discord_webhook_url` VARCHAR(255) NULL AFTER `recovery_code_hash`",
                    'autolock_minutes' => "ALTER TABLE `users` ADD COLUMN `autolock_minutes` INT NOT NULL DEFAULT 15 AFTER `discord_webhook_url`",
                    'failed_login_attempts' => "ALTER TABLE `users` ADD COLUMN `failed_login_attempts` INT NOT NULL DEFAULT 0 AFTER `autolock_minutes`",
                    'locked_until' => "ALTER TABLE `users` ADD COLUMN `locked_until` DATETIME NULL AFTER `failed_login_attempts`",
                ];

                foreach ($userColMigrations as $col => $sql) {
                    if (!in_array($col, $columns)) {
                        try {
                            $pdo->exec($sql);
                        } catch (\Throwable $e) {}
                    }
                }

                // Tetapkan role admin default untuk ID 1 jika belum terset
                try {
                    $pdo->exec("UPDATE `users` SET `role` = 'admin' WHERE `id` = 1 AND (`role` IS NULL OR `role` = 'user')");
                } catch (\Throwable $e) {}
            }

            // Pastikan tabel relasional pendukung tersedia
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

            // Pastikan indeks performa terpasang
            $performanceIndexes = [
                ['users', 'idx_user_email', '(`email`)'],
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

        } catch (\Throwable $e) {
            // Error ditangani secara silent agar tidak menghentikan alur aplikasi
        }
    }

    /**
     * Mengeksekusi file SQL secara statement-by-statement agar tahan terhadap multi-query delimiter
     */
    public static function executeSqlFile(PDO $pdo, string $filepath): void {
        if (!file_exists($filepath)) return;
        $content = file_get_contents($filepath);
        if (!$content) return;

        // Pisahkan per baris dan bersihkan komentar single-line
        $lines = explode("\n", $content);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        // Pisahkan berdasarkan delimiter titik koma ';'
        $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (\Throwable $ex) {
                    // Lanjutkan jika tabel atau indeks sudah ada
                }
            }
        }
    }

    /**
     * Menangani kegagalan koneksi database secara anggun (JSON untuk API, HTML auto-refresh untuk Web)
     */
    private static function handleConnectionFailure(?\Throwable $e): void {
        $isApi = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/api/'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        http_response_code(503);

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Layanan database sedang melakukan inisialisasi atau belum siap. Silakan coba kembali dalam beberapa detik.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="4">
    <title>Inisialisasi Database — GembokX</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center shadow-2xl">
        <div class="inline-flex p-4 bg-amber-500/10 rounded-2xl text-amber-400 mb-5 animate-pulse">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
        </div>
        <h1 class="text-xl font-bold tracking-tight mb-2">Memulai Layanan Database</h1>
        <p class="text-sm text-slate-400 mb-6 leading-relaxed">
            Database GembokX sedang bersiap atau melakukan perbaikan skema otomatis. Halaman ini akan memuat ulang secara mandiri.
        </p>
        <div class="flex items-center justify-center gap-2 text-xs text-indigo-400 font-mono bg-indigo-950/40 py-2.5 px-4 rounded-xl border border-indigo-900/50">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Menghubungkan kembali...</span>
        </div>
    </div>
</body>
</html>';
        exit;
    }
}
