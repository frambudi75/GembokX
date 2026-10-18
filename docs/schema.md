# Database Schema & Storage Architecture
## PassVault — Encrypted Storage Schema (v1.0)

---

## 1. Database: `passvault_db` (MySQL / MariaDB)

Semua kolom yang menyimpan data kredensial, catatan, dan nama item brankas disimpan dalam bentuk **Ciphertext (AES-256-GCM)**. Server dan database tidak pernah menyimpan plaintext data sensitif.

---

### 👤 1. Tabel `users`
Menyimpan kredensial otentikasi akun, salt kriptografi, dan kunci brankas terenkripsi (*Protected Symmetric Key*).

```sql
CREATE TABLE users (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    email                   VARCHAR(191) NOT NULL UNIQUE,
    username                VARCHAR(100) NOT NULL,
    auth_hash               VARCHAR(255) NOT NULL,            -- Hash bcrypt dari client-derived Auth Key
    kdf_salt                VARCHAR(64) NOT NULL,             -- Salt unik per-user (Hex / Base64) untuk PBKDF2
    kdf_iterations          INT NOT NULL DEFAULT 600000,      -- Jumlah iterasi PBKDF2 (min 600.000)
    encrypted_vault_key     TEXT NOT NULL,                    -- Kunci simetris brankas yang dienkripsi oleh Master Key
    vault_key_iv            VARCHAR(64) NOT NULL,             -- IV untuk dekripsi kunci brankas
    totp_secret             VARCHAR(64) NULL,                 -- Secret key 2FA TOTP (opsional)
    totp_enabled            TINYINT(1) NOT NULL DEFAULT 0,    -- Status aktif 2FA
    recovery_code_hash      VARCHAR(255) NOT NULL,            -- Hash bcrypt dari emergency recovery key
    failed_login_attempts   INT NOT NULL DEFAULT 0,           -- Penghitung percobaan login gagal
    locked_until            DATETIME NULL,                    -- Waktu kunci sementara akun (brute-force defense)
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 📁 2. Tabel `folders`
Menyimpan kategori/folder untuk mengelompokkan item brankas. Nama folder dienkripsi demi menjaga kerahasiaan (*zero-knowledge*).

```sql
CREATE TABLE folders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    encrypted_name  TEXT NOT NULL,                            -- Nama folder terenkripsi (AES-256-GCM)
    iv              VARCHAR(64) NOT NULL,                     -- IV enkripsi nama folder
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_folder_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_folder_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 🔐 3. Tabel `vault_items`
Menyimpan seluruh rekaman kata sandi, catatan rahasia, kartu perbankan, dan data identitas dalam bentuk payload JSON terenkripsi.

```sql
CREATE TABLE vault_items (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT NOT NULL,
    folder_id           INT NULL,
    item_type           ENUM('login', 'note', 'card', 'identity') NOT NULL DEFAULT 'login',
    is_favorite         TINYINT(1) NOT NULL DEFAULT 0,
    encrypted_payload   LONGTEXT NOT NULL,                    -- JSON string terenkripsi (nama item, username, password, url, notes, dll)
    iv                  VARCHAR(64) NOT NULL,                 -- Initialization Vector 96-bit (Base64)
    auth_tag            VARCHAR(64) NOT NULL,                 -- GCM Authentication Tag (Integritas data)
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vault_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vault_folder FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL,
    INDEX idx_vault_user (user_id),
    INDEX idx_vault_type (user_id, item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Struktur Data JSON di dalam `encrypted_payload` (Setelah didekripsi di browser):
```json
// Contoh Payload Tipe "login":
{
  "name": "GitHub Personal",
  "username": "habib",
  "password": "SuperSecretPassword123!#",
  "url": "https://github.com/login",
  "notes": "API Token tersimpan di server 2",
  "totp_seed": "JBSWY3DPEHPK3PXP"
}

// Contoh Payload Tipe "card":
{
  "name": "BCA Visa Contactless",
  "cardholder": "HABIB",
  "brand": "Visa",
  "number": "4000123456789010",
  "exp_month": "12",
  "exp_year": "2028",
  "cvv": "123",
  "notes": "Kartu utama transaksi online"
}
```

---

### 🛡️ 4. Tabel `activity_logs`
Mencatat riwayat audit keamanan (misal: login berhasil, login gagal, ekspor data) untuk deteksi aktivitas mencurigakan.

```sql
CREATE TABLE activity_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    event_type  VARCHAR(50) NOT NULL,                         -- login_success, login_failed, 2fa_enabled, export_vault
    ip_address  VARCHAR(45) NOT NULL,
    user_agent  TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_log_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2. Mekanisme Migrasi Otomatis (Auto-Migration)
Saat aplikasi diakses untuk pertama kali di XAMPP atau Docker:
1. `app/Config/Database.php` mendeteksi apakah tabel-tabel utama sudah ada.
2. Jika belum, skrip `schema.sql` akan otomatis dieksekusi secara transaksional.
3. Tidak diperlukan perintah terminal migrasi manual di cPanel.
