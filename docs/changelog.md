# 📜 Changelog — PassVault Zero-Knowledge Password Manager

Semua pembaruan dan catatan rilis PassVault dicatat secara kronologis dalam dokumen ini.

---

## [v2.2.0] - 2026-10-18
### ⚡ Redis Caching Engine & Database Performance Indexing
- **Redis Cache Integration:** Menambahkan `App\Services\CacheService` dengan dukungan otomatis:
  1. Ekstensi native PHP `\Redis` jika tersedia.
  2. Pure PHP RESP Socket client murni tanpa dependensi eksternal untuk komunikasi langsung port 6379.
  3. Seamless file-cache fallback jika Redis server tidak aktif sehingga aplikasi tidak pernah mengalami crash.
- **Microsecond User Lookups:** `SessionService::user()` kini di-cache dengan invalidasi otomatis saat profil atau role diubah.
- **Database B-Tree Indexing:** Menambahkan indeks komposit dan indeks kolom tunggal pada:
  - `users`: `idx_user_username`, `idx_user_role_status`, `idx_user_created_at`.
  - `vault_items`: `idx_vault_user_folder`, `idx_vault_user_updated`, `idx_vault_user_created`.
  - `folders`: `idx_folder_user_created`.
  - `activity_logs`: `idx_log_event`.
  - `shared_items`: `idx_share_recipient_created`, `idx_share_sender_created`.
  - `secret_links`: `idx_secret_expiry`, `idx_secret_user_created`.
- **Auto-Migration on Boot:** `Database::ensureSchema()` otomatis memeriksa dan memasang indeks performa baru pada database yang sudah berjalan.

### 🔌 Companion Browser Extension (Manifest V3)
- Ekstensi browser resmi untuk Google Chrome, Microsoft Edge, Brave, dan Opera.
- **Quick Password Generator:** Generator kata sandi acak dengan standar entropi tinggi dari toolbar browser.
- **One-Click Autofill:** Deteksi otomatis formulir login username dan password pada tab yang aktif.
- **Direct Search & Vault Access:** Cari kredensial instan tanpa membuka tab baru.

---

## [v2.1.0] - 2026-10-18
### 👆 WebAuthn Biometric Unlock
- Buka brankas instan dengan Windows Hello, Apple Touch ID / Face ID, atau sensor sidik jari perangkat tanpa mengetik ulang Master Password di setiap sesi.
- Kunci brankas dienkripsi lokal dengan kunci kredensial WebAuthn yang terikat pada hardware authenticator.

### 🔗 Zero-Knowledge Burn-After-Reading Secrets & Peer Sharing
- **Burn-After-Reading Link Generator:** Bagikan catatan atau sandi rahasia yang otomatis musnah setelah 1 kali dibuka atau melewati batas waktu (1 jam s/d 7 hari).
- **URL Fragment Encryption:** Kunci AES didefinisikan pada fragment `#key` URL yang menurut standar RFC tidak pernah dikirimkan ke server.
- **Peer-to-Peer Vault Sharing:** Bagikan kredensial langsung antar akun pengguna PassVault.

### 🖨️ Printable Emergency Kit PDF
- Lembar pemulihan fisik berstandar perbankan di rute `/emergency-kit` siap dicetak atau disimpan sebagai PDF.
- Memuat QR Code login, token pemulihan, salt KDF, dan instruksi pembukaan brankas.

### 🕵️ HaveIBeenPwned (HIBP) k-Anonymity Dark Web Checker
- Modul audit kata sandi memeriksa kebocoran data (*data breach*) global secara privat menggunakan k-Anonymity (hanya 5 karakter pertama dari SHA-1 yang dikirim ke API publik).

### ⏱️ Auto-Clear Clipboard
- Penghapusan sandi otomatis dari clipboard setelah 15 detik, 30 detik, atau 60 detik dengan progress toast interaktif.

### 🖼️ Smart 10MB Avatar Auto-Compression
- Dukungan unggah foto profil hingga 10MB dengan kompresi Canvas client-side dan PHP GD WebP server-side (< 40 KB).

---

## [v2.0.0] - 2026-10-17
### 🔐 Zero-Knowledge Core & PWA
- Enkripsi end-to-end klien menggunakan Web Crypto API (AES-256-GCM + PBKDF2 600.000 iterasi).
- Otentikasi Dua Faktor (2FA TOTP) dengan QR Code Barcode untuk Google Authenticator / Authy.
- Progressive Web App (PWA) dengan Service Worker dan kemampuan instalasi all-device.
- Manajemen Pengguna (Admin User Manager) untuk mendaftarkan akun baru secara terpusat.
