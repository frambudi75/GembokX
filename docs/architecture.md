# System Architecture & Infrastructure
## PassVault — Zero-Knowledge Password Manager (v1.0)

---

## 1. Arsitektur Stack & Cryptographic Data Flow

```
┌────────────────────────────────────────────────────────────────────────┐
│                        BROWSER / CLIENT-SIDE                           │
│  • Tailwind CSS UI (Clean, Modern, Accessible, Dark/Light Mode)        │
│  • Web Crypto API (SubtleCrypto Native Browser)                        │
│    - PBKDF2 (600,000 iterasi) -> Master Key & Auth Key Derivation     │
│    - AES-256-GCM -> Encrypt / Decrypt Vault Items di Sisi Klien       │
│  • Vault Manager (Memori RAM hanya saat brankas terbuka, auto-clear)   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ HTTP JSON Payload (Ciphertext Only)
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                       PHP NATIVE MVC APPLICATION                       │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │ index.php (Front Controller & Dynamic Subfolder Router)          │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │ MIDDLEWARE & GUARDS                                              │  │
│  │ ├── CsrfMiddleware.php (Cryptographic anti-CSRF token)           │  │
│  │ ├── AuthMiddleware.php (Session check & vault lock state)        │  │
│  │ ├── RateLimitMiddleware.php (Anti brute-force login)             │  │
│  │ └── SecurityHeaderMiddleware.php (CSP, HSTS, X-Frame, X-XSS)     │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │ CONTROLLERS                                                      │  │
│  │ ├── AuthController.php (Login, Register, 2FA, Logout)            │  │
│  │ ├── VaultController.php (Dashboard view & HTML render)           │  │
│  │ ├── ApiVaultController.php (JSON Sync: Simpan/Ambil Ciphertext)  │  │
│  │ ├── FolderController.php (CRUD folder kategori)                  │  │
│  │ ├── SettingsController.php (Profil, 2FA TOTP setup)              │  │
│  │ └── ImportExportController.php (Export ciphertext / Import CSV)  │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │ SERVICES                                                         │  │
│  │ ├── TotpService.php (RFC 6238 2FA Generator & Verifier)          │  │
│  │ ├── SessionService.php (Secure session cookie & rotation)        │  │
│  │ └── AuditLogService.php (Pencatatan aktivitas login & security)  │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │ MODELS (PDO MySQL Prepared Statements)                           │  │
│  │ ├── UserModel.php (User auth data & encrypted master symmetric)  │  │
│  │ ├── VaultItemModel.php (Ciphertext, IV, Auth Tag, Item Type)     │  │
│  │ └── FolderModel.php (Folder / label pengelompokkan)              │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                   │
│                                   ▼
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │                      DATABASE STORAGE                            │  │
│  │  • MySQL 5.7+ / MariaDB 10.3+ (InnoDB, utf8mb4)                  │  │
│  │  • Hanya menyimpan Ciphertext teracak (Zero-Knowledge)           │  │
│  │  • Auto-migration saat pertama kali dijalankan                   │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Struktur Direktori Proyek (Clean Architecture)

```
pwd/
├── app/
│   ├── Config/
│   │   ├── Database.php          # Koneksi PDO dengan auto-reconnect & utf8mb4
│   │   ├── App.php               # Konfigurasi app, timezone, dan env loader
│   │   └── Security.php          # Definisi CSP headers dan parameter hashing
│   │
│   ├── Controllers/
│   │   ├── AuthController.php    # Registrasi, login verifikasi, logout
│   │   ├── VaultController.php   # Render halaman brankas utama
│   │   ├── ApiVaultController.php# Simpan, perbarui, dan hapus ciphertext vault
│   │   ├── FolderController.php  # Pengelolaan folder organisasi
│   │   ├── SettingsController.php# Setup 2FA TOTP, ubah master auth key
│   │   └── ExportController.php  # Backup vault dan import Bitwarden/Chrome CSV
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php    # Validasi session aktif
│   │   ├── CsrfMiddleware.php    # Pengecekan token CSRF per request
│   │   └── RateLimiter.php       # Throttling percobaan login
│   │
│   ├── Models/
│   │   ├── User.php              # Query tabel users
│   │   ├── VaultItem.php         # Query tabel vault_items (CRUD ciphertext)
│   │   ├── Folder.php            # Query tabel folders
│   │   └── ActivityLog.php       # Query audit login & aktivitas akun
│   │
│   ├── Services/
│   │   ├── TotpService.php       # Implementasi RFC 6238 TOTP murni PHP
│   │   └── SessionService.php    # Enkapsulasi session aman (HttpOnly, SameSite)
│   │
│   └── Helpers/
│       ├── Router.php            # Router dinamis (otomatis mendeteksi subfolder XAMPP vs cPanel)
│       └── View.php              # Helper render template HTML
│
├── docker/
│   ├── Dockerfile                # Image PHP 8.2 Apache
│   ├── apache.conf               # VirtualHost & mod_rewrite configuration
│   └── php.ini                   # Tuning keamanan PHP (expose_php=Off, etc.)
│
├── docs/                         # Seluruh dokumentasi teknis
│   ├── prd.md                    # Product Requirement Document
│   ├── architecture.md           # Dokumen arsitektur ini
│   ├── schema.md                 # Skema tabel database
│   ├── rules.md                  # Aturan coding & security
│   └── readme.md                 # Panduan cepat
│
├── public/                       # Web Root (DocumentRoot)
│   ├── assets/
│   │   ├── css/                  # File CSS / Tailwind
│   │   └── js/
│   │       ├── crypto.js         # Core Engine Web Crypto (PBKDF2 + AES-GCM)
│   │       ├── vault.js          # Manajemen state UI brankas di browser
│   │       ├── generator.js      # Generator kata sandi acak & passphrase
│   │       └── app.js            # Interaksi antarmuka umum (modal, toast, copy)
│   ├── index.php                 # Front controller utama
│   └── .htaccess                 # Rewrite engine untuk mod_rewrite Apache
│
├── views/
│   ├── layouts/
│   │   ├── main.php              # Layout utama dashboard
│   │   └── auth.php              # Layout halaman login & register
│   ├── partials/
│   │   ├── navbar.php            # Header, search bar, status lock, avatar
│   │   ├── sidebar.php           # Filter tipe item (Login, Card, Note), folder
│   │   ├── modal_item.php        # Modal tambah/edit item vault
│   │   └── modal_generator.php   # Modal password generator
│   └── vault/
│       ├── index.php             # Tampilan daftar item dan detail
│       ├── login.php             # Form login & input master password
│       ├── register.php          # Form register & pembuatan master key
│       ├── settings.php          # Pengaturan akun, ganti password, 2FA
│       └── audit.php             # Laporan keamanan password
│
├── .env.example                  # Template konfigurasi environment
├── .htaccess                     # Root rewrite (mengarahkan request ke /public)
├── docker-compose.yml            # Orchestrasi container App + MySQL
└── README.md                     # Ringkasan proyek
```

---

## 3. Kompatibilitas Multi-Lingkungan

### A. XAMPP (Local Subfolder)
* **Karakteristik:** Aplikasi berjalan di subfolder, misal: `http://localhost/pwd/`.
* **Solusi Router:** Helper `Router.php` memotong `dirname($_SERVER['SCRIPT_NAME'])` sehingga rute `http://localhost/pwd/login` diproses identik dengan `/login` di server production tanpa perlu konfigurasi manual.
* **Root `.htaccess`:** Otomatis meneruskan request dari root folder proyek ke `public/index.php`.

### B. cPanel / Shared Hosting (Production)
* **Karakteristik:** Aplikasi ditempatkan di `public_html/` atau subdomain tanpa akses terminal root.
* **Solusi:**
  1. Konten `public/` dapat langsung menjadi isi `public_html/` atau menggunakan file root `.htaccess` yang sudah disediakan.
  2. Menggunakan ekstensi native standar: `ext-pdo_mysql`, `ext-openssl`, `ext-mbstring`, `ext-session`. Tidak membutuhkan Composer di server produksi jika library dikemas secara *standalone*.

### C. Docker
* **Karakteristik:** Standar kontainer terisolasi multi-layanan.
* **Solusi:** `docker-compose up -d` menjalankan 3 service terkoordinasi:
  1. `web`: PHP 8.2 Apache dengan DocumentRoot `/var/www/html/public`.
  2. `db`: MariaDB 10.11 dengan volume persisten data dan healthcheck.
  3. `redis`: Redis 7 Alpine untuk akselerasi cache memori ultra-cepat.

### D. Redis Cache & Socket Driver
* **Cache Layer:** `App\Services\CacheService` mendukung native `\Redis` extension dan pure PHP RESP Socket client.
* **High Reliability:** Otomatis fallback ke file-cache tanpa error saat Redis offline.

### E. Companion Browser Extension
* **Standar Manifest V3:** Mendukung Google Chrome, Microsoft Edge, Brave.
* **Fitur:** Password generator instan, smart autofill login form, quick vault search.

