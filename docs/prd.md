# Product Requirement Document (PRD)
## PassVault — Self-Hosted Zero-Knowledge Password Manager (v1.0)

---

## 1. Ringkasan Produk
**PassVault (PWD)** adalah aplikasi manajemen kata sandi pribadi (*Personal Password Manager*) berbasis web yang dirancang dengan arsitektur **Zero-Knowledge Encryption** (terinspirasi dari Bitwarden & 1Password). Dibangun menggunakan **PHP Native MVC**, **Tailwind CSS**, dan **MySQL/MariaDB**.

Pengguna cukup mengingat **1 Master Password** untuk mengontrol, menyimpan, dan mengelola ratusan password akun (email, media sosial, perbankan, server/SSH, API keys) secara terpusat, aman, dan mudah diakses.

### Prinsip Utama Keamanan: Zero-Knowledge
* Seluruh data vault dienkripsi dan didekripsi langsung di browser pengguna (**Client-Side**) menggunakan **Web Crypto API** (PBKDF2 600.000 iterasi + AES-256-GCM).
* Server PHP dan database MySQL **tidak pernah menerima atau menyimpan Master Password**, dan **tidak pernah bisa membaca plaintext password** yang tersimpan di brankas.
* Jika database server atau hosting bocor, data yang terlihat hanyalah deretan ciphertext acak yang tidak dapat didekripsi tanpa Master Password milik pengguna.

---

## 2. Target Deployment & Kompatibilitas
Aplikasi dirancang modular agar dapat berjalan tanpa modifikasi kode di tiga lingkungan utama:
1. **XAMPP (Lokal Windows/Linux/Mac):** Dijalankan di subfolder, misal `http://localhost/pwd/`.
2. **cPanel / Shared Hosting:** Dijalankan di `public_html` (root domain) atau subdomain tanpa butuh akses root SSH atau dependensi langka.
3. **Docker Container:** Tersedia konfigurasi `Dockerfile` (PHP Apache) & `docker-compose.yml` (App + MariaDB) sekali klik.

---

## 3. Rincian Modul & Fitur

### 🔐 1. Autentikasi, Master Key & Kriptografi
| Fitur | Keterangan |
|-------|------------|
| Registrasi Akun & Master Password | Pengguna membuat akun dengan Master Password kuat. Master Password diolah menjadi Auth Key (untuk verifikasi login di server) dan Encryption Key (tersimpan hanya di memori browser). |
| Master Key Derivation | Menggunakan algoritma **PBKDF2** dengan minimal **600.000 iterasi** (standar OWASP) dan salt unik per user. |
| Brankas Enkripsi AES-256-GCM | Setiap item vault dienkripsi dengan symmetric key menggunakan AES-GCM 256-bit dan Initialization Vector (IV) acak yang unik. |
| Otentikasi Dua Faktor (2FA / TOTP) | Dukungan autentikasi berbasis waktu (Google Authenticator / Authy / Aegis) menggunakan PHP Native RFC 6238. |
| Emergency Recovery Code | Kode pemulihan acak yang digenerate saat registrasi untuk mereset akses jika pengguna lupa master key. |
| Auto-Lock Brankas | Brankas otomatis terkunci setelah periode waktu tidak aktif (idle timer: 5, 15, 30 menit). |
| Proteksi Brute-Force & Rate Limiting | Kunci sementara IP/User jika terjadi kegagalan login berturut-turut (>5 kali). |

### 🗄️ 2. Manajemen Item Vault (Brankas Kata Sandi)
| Kategori Item | Detail Bidang yang Disimpan (Semua Terenkripsi) |
|---------------|------------------------------------------------|
| **Logins** | Nama Akun, Username/Email, Kata Sandi, URL/Situs Web, Catatan, TOTP Secret Key (opsional). |
| **Secure Notes** | Judul Catatan, Isi Catatan Rahasia (dukungan multi-line, kunci SSH, recovery seeds, PIN). |
| **Cards (Kartu)** | Nama Pemilik, Nomor Kartu, Brand (Visa/Mastercard), Bulan/Tahun Kadaluarsa, CVV/Kode Keamanan. |
| **Identities** | Nama Lengkap, No. KTP/Paspor, Perusahaan, Alamat, Nomor Telepon, Email Pribadi. |

### ⚡ 3. Password Generator Terintegrasi
* Generator password acak yang aman secara kriptografi (`crypto.getRandomValues`).
* Opsi kustomisasi fleksibel:
  * Panjang karakter (8 hingga 64 karakter).
  * Karakter huruf besar (`A-Z`), huruf kecil (`a-z`), angka (`0-9`), dan simbol spesial (`!@#$%^&*`).
  * Opsi menghindari karakter membingungkan (misal: `1, l, I, 0, O`).
  * Mode **Passphrase** (kata acak bergaya Diceware, mudah dihafal namun berentropi tinggi).
* Indikator kekuatan password (*password strength meter*) waktu-nyata.

### 📁 4. Organisasi, Pencarian & UX
* **Folder & Kategori:** Pengelompokkan item ke dalam folder kustom (misal: *Pekerjaan, Sosial Media, Finansial, Server*).
* **Favorit / Pinned:** Menandai item penting agar selalu muncul di bagian atas brankas.
* **Pencarian Cepat (0ms):** Filter dan pencarian instan berbasis teks di sisi browser terhadap item yang sudah didekripsi di memori.
* **Salin Satu-Klik (Copy with Feedback):** Tombol copy username/password instan dengan visual toast feedback.
* **Clipboard Auto-Clear:** Otomatis menghapus kata sandi yang disalin ke clipboard sistem setelah 30–60 detik demi keamanan.
* **Show/Hide Password:** Masking password secara default dengan ikon mata toggle.

### 🛡️ 5. Pemeriksaan Kesehatan Brankas (Security Audit)
* **Weak Password Checker:** Mendeteksi password yang terlalu pendek (<10 karakter) atau memiliki entropi rendah.
* **Reused Password Checker:** Mendeteksi penggunaan password yang sama di beberapa akun berbeda.
* *Catatan:* Seluruh analisis audit dilakukan 100% lokal di browser pengguna tanpa mengirim data ke pihak ketiga.

### 📤 6. Import & Export Data
* **Ekspor Brankas:** Opsi ekspor ke format JSON terenkripsi (aman untuk backup) atau format CSV terbuka.
* **Impor Brankas:** Mendukung impor langsung dari file CSV ekspor Bitwarden, Google Chrome Password Manager, atau Firefox.

---

## 4. Alur Routing & Struktur Halaman
| No | Halaman / Fitur | Rute | Keterangan |
|:--:|-----------------|------|------------|
| 1 | Login & Unlock | `?action=login` | Form login email + master password, input 2FA TOTP |
| 2 | Registrasi Akun | `?action=register` | Pembuatan akun baru & pembuatan master key |
| 3 | Dashboard Vault | `/` atau `?action=vault` | Tampilan utama brankas, daftar item, folder, search |
| 4 | Tambah / Edit Item | AJAX Modal di Dashboard | Modal enkripsi/dekripsi item tanpa reload halaman |
| 5 | Password Generator | `?action=generator` atau Modal | Generator password mandiri atau saat menambah item |
| 6 | Pengaturan Akun | `?action=settings` | Ganti master password, setup 2FA, kelola folder |
| 7 | Security Audit | `?action=audit` | Ringkasan kesehatan password brankas |
| 8 | Import & Export | `?action=export` | Manajemen backup dan migrasi data |
| 9 | API Vault Sync | `?action=api_sync` | Endpoint JSON (POST/GET) sinkronisasi ciphertext |

---

## 5. Non-Functional Requirements
1. **Performa:** Waktu render dashboard < 200ms pada shared hosting biasa.
2. **Keamanan:** Mematuhi OWASP Top 10 (CSRF Protection, XSS Sanitization, SQL Injection Prepared Statements, HTTPS enforcement, CSP Headers).
3. **Responsif:** Tampilan optimal di smartphone (layar sentuh), tablet, dan laptop/desktop via Tailwind CSS.
4. **Desain:** Modern, clean, fungsional, dan bebas dari elemen dekoratif AI yang tidak bertujuan (*Anti-Slop standard*).
