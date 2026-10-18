-- Schema Database PassVault (Zero-Knowledge Password Manager)
-- Karakteristik: MySQL 5.7+ / MariaDB 10.3+ (InnoDB, UTF-8 mb4)

CREATE TABLE IF NOT EXISTS `users` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `email`                 VARCHAR(191) NOT NULL UNIQUE,
    `username`              VARCHAR(100) NOT NULL,
    `role`                  ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `full_name`             VARCHAR(150) NULL,
    `avatar`                VARCHAR(255) NULL,
    `phone`                 VARCHAR(30) NULL,
    `is_active`             TINYINT(1) NOT NULL DEFAULT 1,
    `auth_hash`             VARCHAR(255) NOT NULL,            -- Hash bcrypt dari client-derived Auth Key
    `kdf_salt`              VARCHAR(64) NOT NULL,             -- Salt unik per-user (Hex / Base64) untuk KDF
    `kdf_iterations`        INT NOT NULL DEFAULT 600000,      -- Jumlah iterasi KDF (PBKDF2) atau waktu (Argon2id)
    `kdf_algorithm`         ENUM('pbkdf2', 'argon2id') NOT NULL DEFAULT 'pbkdf2', -- Algoritma KDF (PBKDF2 atau Argon2id)
    `kdf_memory`            INT NOT NULL DEFAULT 65536,       -- Memori Argon2id dalam KB (default 64MB)
    `kdf_parallelism`       INT NOT NULL DEFAULT 4,           -- Derajat paralelisme thread Argon2id
    `encrypted_vault_key`   TEXT NOT NULL,                    -- Kunci simetris brankas yang dienkripsi oleh Master Key
    `vault_key_iv`          VARCHAR(64) NOT NULL,             -- IV untuk dekripsi kunci brankas
    `totp_secret`           VARCHAR(64) NULL,                 -- Secret key 2FA TOTP (opsional)
    `totp_enabled`          TINYINT(1) NOT NULL DEFAULT 0,    -- Status aktif 2FA (0/1)
    `recovery_code_hash`    VARCHAR(255) NOT NULL,            -- Hash bcrypt dari emergency recovery key
    `discord_webhook_url`   VARCHAR(255) NULL,                -- Webhook URL Discord untuk notifikasi keamanan instan
    `autolock_minutes`      INT NOT NULL DEFAULT 15,          -- Durasi inaktivitas auto-lock brankas (menit, 0=nonaktif)
    `failed_login_attempts` INT NOT NULL DEFAULT 0,           -- Penghitung percobaan login gagal
    `locked_until`          DATETIME NULL,                    -- Waktu kunci sementara akun (brute-force defense)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (email),
    INDEX idx_user_username (username),
    INDEX idx_user_role_status (role, is_active),
    INDEX idx_user_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS folders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    encrypted_name  TEXT NOT NULL,
    iv              VARCHAR(64) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_folder_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_folder_user (user_id),
    INDEX idx_folder_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vault_items (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    folder_id         INT NULL,
    item_type         ENUM('login', 'note', 'card', 'identity') NOT NULL DEFAULT 'login',
    is_favorite       TINYINT(1) NOT NULL DEFAULT 0,
    encrypted_payload LONGTEXT NOT NULL,
    iv                VARCHAR(64) NOT NULL,
    auth_tag          VARCHAR(64) NOT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vault_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vault_folder FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL,
    INDEX idx_vault_user (user_id),
    INDEX idx_vault_type (user_id, item_type),
    INDEX idx_vault_favorite (user_id, is_favorite),
    INDEX idx_vault_user_folder (user_id, folder_id),
    INDEX idx_vault_user_updated (user_id, updated_at),
    INDEX idx_vault_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    event_type  VARCHAR(50) NOT NULL,
    ip_address  VARCHAR(45) NOT NULL,
    user_agent  TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_log_user (user_id, created_at),
    INDEX idx_log_event (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shared_items (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    sender_id         INT NOT NULL,
    recipient_id      INT NOT NULL,
    item_type         ENUM('login', 'note', 'card', 'identity') NOT NULL DEFAULT 'login',
    encrypted_payload LONGTEXT NOT NULL,
    iv                VARCHAR(64) NOT NULL,
    auth_tag          VARCHAR(64) NOT NULL DEFAULT 'gcm-embedded',
    note              VARCHAR(255) NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_share_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_share_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_share_recipient (recipient_id),
    INDEX idx_share_recipient_created (recipient_id, created_at),
    INDEX idx_share_sender_created (sender_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS secret_links (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    link_token        VARCHAR(64) NOT NULL UNIQUE,
    encrypted_payload LONGTEXT NOT NULL,
    iv                VARCHAR(64) NOT NULL,
    auth_tag          VARCHAR(64) NOT NULL DEFAULT 'gcm-embedded',
    views_left        INT NOT NULL DEFAULT 1,
    expires_at        DATETIME NOT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_secret_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_secret_token (link_token),
    INDEX idx_secret_expiry (expires_at, views_left),
    INDEX idx_secret_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

