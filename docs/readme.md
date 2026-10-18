# 🔐 PassVault — Dokumentasi Lengkap (v1.0)

Aplikasi brankas dan manajemen kata sandi pribadi (*Personal Password Manager*) berbasis **PHP Native MVC**, **Tailwind CSS**, dan **MySQL/MariaDB**, dengan arsitektur keamanan **Zero-Knowledge Encryption** berbasis **Web Crypto API**. 

Mendukung deployment mudah di **XAMPP (Lokal)**, **cPanel / Shared Hosting**, dan **Docker Container**.

---

## 📋 Indeks Berkas Dokumentasi

| No | Dokumen | Deskripsi |
|:--:|---------|-----------|
| 1 | [prd.md](prd.md) | **Product Requirements Document** — Spesifikasi lengkap seluruh fitur brankas, generator, dan modul keamanan |
| 2 | [architecture.md](architecture.md) | **System Architecture** — Arsitektur kriptografi Zero-Knowledge, alur routing, struktur direktori MVC |
| 3 | [schema.md](schema.md) | **Database Schema** — Struktur tabel MySQL penyimpanan ciphertext terenkripsi (AES-256-GCM) |
| 4 | [rules.md](rules.md) | **Development Rules** — Aturan ketat keamanan OWASP, penanganan memori & standar UI Tailwind |

---

## 🚀 Panduan Memulai Cepat (Quick Start)

### 1. Menjalankan di XAMPP (Local Subfolder)
1. Pastikan modul **Apache** dan **MySQL** di XAMPP Control Panel sudah berjalan.
2. Tempatkan folder proyek di `C:\xampp\htdocs\pwd\`.
3. Buka browser dan akses: `http://localhost/pwd/public/` (atau langsung `http://localhost/pwd/` melalui root redirect `.htaccess`).
4. Database `passvault_db` beserta tabel-tabelnya akan otomatis dibuat saat pertama kali dibuka (*Auto-Migration*).

---

### 2. Menjalankan dengan Docker
```bash
# Build dan jalankan container di background
docker-compose up -d --build

# Akses aplikasi di browser:
# http://localhost:8080
```

---

### 3. Deploy di cPanel / Shared Hosting
1. Unggah seluruh berkas proyek ke direktori `public_html/`.
2. Buat database MySQL di menu **MySQL Databases** cPanel.
3. Sesuaikan kredensial database di file `.env` (atau `app/Config/Database.php`).
4. Akses domain Anda: skema database akan otomatis terpasang.

---

## 🛡️ Rangkuman Fitur Unggulan
- 🔒 **Zero-Knowledge Client-Side Encryption:** Enkripsi AES-256-GCM langsung di browser via Web Crypto API. Server tidak pernah tahu Master Password atau data rahasia Anda.
- 🔑 **Master Key Derivation PBKDF2:** 600.000 iterasi dengan salt unik per pengguna.
- ⚡ **Integrated Password Generator:** Buat password acak yang sangat kuat atau passphrase kata bergaya Diceware.
- 📱 **Responsive & Clean UI:** Tampilan profesional, modern, dan intuitif berbasis Tailwind CSS dengan dukungan Dark & Light Mode.
- 🏷️ **Folder & Kategori:** Mengelompokkan password ke kategori Media Sosial, Pekerjaan, Perbankan, atau Server.
- 📋 **One-Click Copy & Clipboard Timeout:** Salin kredensial instan dengan pembersihan clipboard otomatis setelah 30–60 detik.
- 🔐 **2FA (Two-Factor Authentication):** Dukungan TOTP untuk Google Authenticator / Authy.
- 📦 **Import & Export:** Kompatibel dengan format ekspor CSV Bitwarden dan Google Chrome.
