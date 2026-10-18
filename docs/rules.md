# Security, Architecture & Development Rules
## PassVault — Panduan Pengembangan & Standar Keamanan (v1.0)

---

## 1. Standar Keamanan Mutlak (Non-Negotiable Security Rules)

### 1.1 Zero-Knowledge Enforcement
- 🚫 **DILARANG KERAS** mengirim Master Password plaintext ke server PHP.
- 🚫 **DILARANG KERAS** mendekripsi ciphertext brankas di sisi server (PHP).
- ✅ Seluruh proses enkripsi dan dekripsi wajib dilakukan di browser via **Web Crypto API** (`window.crypto.subtle`).
- ✅ Algoritma enkripsi wajib menggunakan **AES-256-GCM** dengan **IV 96-bit** unik acak per-item.
- ✅ Derivasi kunci wajib menggunakan **PBKDF2-HMAC-SHA256** dengan minimal **600.000 iterasi** dan salt acak per-user.

### 1.2 Database & SQL Injection
- ✅ **Wajib** menggunakan PDO Prepared Statements untuk setiap interaksi database.
- ❌ Tidak ada penggabungan string langsung (*string concatenation*) pada query SQL.
- ✅ Sanitasi tipe data input (casting integer untuk ID, validasi format email).

### 1.3 CSRF & Session Security
- ✅ Setiap form POST dan endpoint API AJAX wajib menyertakan dan memvalidasi token CSRF acak kriptografis.
- ✅ Cookie session PHP wajib dikonfigurasi dengan:
  - `HttpOnly = true` (mencegah pencurian cookie via XSS)
  - `SameSite = 'Strict'` (mencegah CSRF cross-origin)
  - `Secure = true` (otomatis jika diakses via HTTPS)
- ✅ `session_regenerate_id(true)` dipanggil setiap kali status autentikasi berubah (login, ganti password).

### 1.4 Rate Limiting & Anti-Brute Force
- ✅ Endpoint login dibatasi maksimal 5 kali kegagalan per 15 menit per kombinasi IP + Email.
- ✅ Jika terlampaui, akun terkunci sementara dengan pesan error yang tidak membocorkan keberadaan email (*generic error message*).

### 1.5 Proteksi Memori & Clipboard Klien
- ✅ Auto-clear clipboard: Setiap kali pengguna menyalin password via tombol copy, buat timer JavaScript untuk menghapus isi clipboard dalam waktu 30-60 detik.
- ✅ Auto-lock vault: Hapus kunci enkripsi dari memori JavaScript saat user idle lebih dari waktu batas (default 15 menit) atau saat tab ditutup.

---

## 2. Standar Desain Antarmuka (Tailwind CSS & Anti-Slop Guidelines)

### 2.1 Filosofi Visual
- **Fungsional, Bersih, dan Presisi:** Mengikuti standar brankas modern (seperti Bitwarden & 1Password) yang mengutamakan kecepatan akses, hierarki informasi yang jelas, dan kemudahan navigasi.
- **Tipografi Data Sensitif:** Semua kata sandi, kunci recovery, dan token TOTP **wajib** menggunakan font monospace (`font-mono`, misal JetBrains Mono / Courier / monospace) untuk membedakan karakter mirip seperti `1`, `l`, `I`, `0`, `O`.
- **Indikator Kekuatan Password Dinamis:** Bar warna visual (Merah: Lemah, Kuning: Sedang, Hijau: Kuat, Zamrud: Sangat Kuat) dengan kalkulasi entropi matematis.
- **Status Kontras Tinggi:** Mengikuti standar aksesibilitas WCAG AA/AAA agar data terbaca jelas baik di mode terang (*Light Mode*) maupun mode gelap (*Dark Mode*).

### 2.2 Desain Sistem & Token Warna
- **Dark Mode (Default Brankas):** Background `#0B0F19`, Card Panel `#111827`, Border `#1F2937`, Accent Primary Blue `#3B82F6` / Emerald `#10B981`.
- **Light Mode:** Background `#F9FAFB`, Card Panel `#FFFFFF`, Border `#E5E7EB`, Accent Primary Blue `#2563EB`.

---

## 3. Konvensi Kode (PHP Native MVC)

### 3.1 Struktur Respon API JSON
```json
{
    "success": true,
    "message": "Item brankas berhasil disimpan.",
    "data": {}
}
```

### 3.2 Portabilitas Multi-Host (XAMPP vs cPanel vs Docker)
- Gunakan helper `url('/path')` untuk seluruh aset dan tautan rute agar otomatis menyesuaikan path subfolder (misal `/pwd/public/` di XAMPP vs `/` di root domain cPanel).
- Jangan menggunakan absolute hardcoded path di file PHP maupun JavaScript.
