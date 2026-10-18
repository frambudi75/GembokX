# 🔌 PassVault Companion — Browser Extension (Manifest V3)

Ekstensi browser resmi pendamping **PassVault** untuk Google Chrome, Microsoft Edge, Brave, dan browser berbasis Chromium lainnya.

---

## ✨ Fitur Utama
1. **Quick Generator**: Buat kata sandi acak dengan standar entropi tinggi secara instan dari toolbar browser dan salin dengan satu klik.
2. **One-Click Autofill**: Otomatis mendeteksi formulir login (input username/email dan password) pada website yang sedang aktif dan mengisinya secara aman.
3. **Smart Search**: Cari akun dan URL yang tersimpan dalam brankas tanpa perlu membuka tab baru.
4. **Zero-Knowledge Kept**: Ekstensi tidak pernah menyimpan Master Password Anda tanpa izin dan berkomunikasi langsung secara lokal dengan instalasi PassVault Anda.

---

## 🚀 Cara Instalasi di Browser (Chrome / Edge / Brave)

Ekstensi ini menggunakan **Manifest V3** dan dapat di-load secara langsung tanpa perlu Chrome Web Store:

1. Buka browser Anda (Google Chrome / Microsoft Edge / Brave).
2. Di address bar, ketik:
   - Chrome: `chrome://extensions/`
   - Edge: `edge://extensions/`
   - Brave: `brave://extensions/`
3. Aktifkan **"Developer mode"** (Mode Pengembang) di pojok kanan atas halaman ekstensi.
4. Klik tombol **"Load unpacked"** (Muat ekstensi yang belum dibongkar).
5. Pilih folder:
   ```
   d:\xampp\htdocs\pwd\extension
   ```
   *(Atau folder `extension` di dalam direktori PassVault Anda)*.
6. Ekstensi **PassVault Companion** akan muncul di toolbar browser Anda!
7. Klik ikon pin (📌) agar PassVault selalu terlihat di taskbar/toolbar browser.

---

## 🔒 Privasi & Keamanan
- Berjalan 100% lokal di browser Anda.
- Hanya berkomunikasi dengan backend PassVault lokal (`http://localhost/pwd`).
- Menggunakan `crypto.getRandomValues()` bawaan Web Crypto API browser untuk memastikan keacakan kriptografis yang tidak dapat ditebak.
