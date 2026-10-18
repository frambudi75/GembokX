<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Akun & Keamanan</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola profil pengguna, foto, autentikasi 2FA barcode, dan cadangan brankas.</p>
        </div>
        <a href="<?= $url('/vault') ?>" class="px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition border border-gray-200 dark:border-gray-700">
            Kembali ke Brankas
        </a>
    </div>

    <!-- Profil Pengguna & Foto -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-xs space-y-6">
        <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Profil Pengguna</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Perbarui informasi identitas pribadi dan foto profil akun Anda.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-5 border-b border-gray-100 dark:border-gray-800">
            <!-- Avatar Display & Upload -->
            <div class="relative group">
                <div id="avatar-preview-container" class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                    <?php if (!empty($user['avatar'])): ?>
                        <img id="avatar-img" src="<?= $url($e($user['avatar'])) ?>" class="w-full h-full object-cover" alt="Foto Profil">
                    <?php else: ?>
                        <div id="avatar-fallback" class="w-full h-full bg-blue-600 text-white font-bold flex items-center justify-center text-2xl uppercase">
                            <?= substr($e($user['username'] ?? 'U'), 0, 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <label class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl cursor-pointer shadow-xs transition inline-flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Ganti Foto Profil</span>
                        <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp,image/gif" onchange="handleAvatarUpload(event)" class="hidden">
                    </label>
                    <span id="avatar-upload-status" class="text-xs text-gray-400 hidden">Mengunggah...</span>
                </div>
                <p class="text-[11px] text-gray-400">Mendukung format JPG, PNG, WEBP, atau GIF (Maks. 10MB, otomatis terkompresi super ringan).</p>
            </div>
        </div>

        <!-- Form Ubah Data Profil -->
        <form id="form-update-profile" onsubmit="handleProfileUpdate(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" id="profile-fullname" value="<?= $e($user['full_name'] ?? '') ?>" placeholder="Masukkan nama lengkap Anda..." class="w-full px-3.5 py-2 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pengguna (Username) *</label>
                    <input type="text" id="profile-username" required value="<?= $e($user['username'] ?? '') ?>" placeholder="username_anda" class="w-full px-3.5 py-2 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 text-gray-900 dark:text-white font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Email (Akun Utama)</label>
                    <input type="email" value="<?= $e($user['email'] ?? '') ?>" disabled class="w-full px-3.5 py-2 text-xs bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl cursor-not-allowed font-mono">
                    <span class="text-[10px] text-gray-400 mt-1 block">Email tertaut dengan identitas brankas terenkripsi Anda.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">No. WhatsApp / Telepon</label>
                    <input type="text" id="profile-phone" value="<?= $e($user['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 text-gray-900 dark:text-white">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Peran Akun:</span>
                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                        <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50 rounded">Administrator</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded">Pengguna</span>
                    <?php endif; ?>
                </div>

                <button type="submit" id="btn-save-profile" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- Modul 2FA TOTP dengan Barcode / QR Code -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Autentikasi Dua Faktor (2FA - TOTP Barcode)</h3>
                    <?php if (!empty($user['totp_enabled'])): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                            Aktif Terlindungi
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                            Belum Diaktifkan
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xl">
                    Pindai barcode QR Code langsung menggunakan aplikasi Google Authenticator, Authy, atau Aegis di ponsel Anda.
                </p>
            </div>
            <div>
                <?php if (!empty($user['totp_enabled'])): ?>
                    <button type="button" onclick="disable2fa()" class="px-3.5 py-2 text-xs font-semibold text-rose-600 hover:text-white hover:bg-rose-600 border border-rose-200 dark:border-rose-900/60 rounded-xl transition">
                        Nonaktifkan 2FA
                    </button>
                <?php else: ?>
                    <button type="button" onclick="startSetup2fa()" id="btn-start-2fa" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Aktifkan 2FA dengan Barcode
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2FA Setup Box dengan Scannable QR Code (Barcode) -->
        <div id="setup-2fa-box" class="hidden mt-4 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-5">
            <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-2xl border border-gray-200 dark:border-gray-700">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white mb-3 flex items-center space-x-1.5">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                    <span>Pindai Barcode (QR Code) di Aplikasi Authenticator</span>
                </h4>

                <div class="flex flex-col md:flex-row items-center gap-6">
                    <!-- Wadah Render Barcode QR Code -->
                    <div class="p-3 bg-white rounded-xl shadow-md border border-gray-200 dark:border-gray-600 flex-shrink-0">
                        <div id="qrcode-container" class="w-[180px] h-[180px] flex items-center justify-center">
                            <!-- QR Canvas / SVG dirender di sini via qrcode.min.js -->
                            <span class="text-xs text-gray-400 animate-pulse">Membuat barcode...</span>
                        </div>
                    </div>

                    <!-- Petunjuk & Kunci Manual -->
                    <div class="space-y-3 flex-1 text-xs">
                        <div class="space-y-1">
                            <p class="font-medium text-gray-800 dark:text-gray-200">Cara Memindai:</p>
                            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-400 text-[11px]">
                                <li>Buka aplikasi <strong>Google Authenticator</strong>, <strong>Authy</strong>, atau <strong>Aegis</strong> di HP.</li>
                                <li>Pilih opsi <strong>Pindai Kode QR / Barcode</strong> (+).</li>
                                <li>Arahkan kamera HP ke barcode di samping.</li>
                            </ol>
                        </div>

                        <!-- Kunci Rahasia Manual -->
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block mb-1">Atau masukkan Kunci Rahasia secara manual:</span>
                            <div class="flex items-center space-x-2">
                                <span id="display-2fa-secret" class="px-2.5 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono font-bold text-blue-600 dark:text-blue-400 tracking-wider select-all"></span>
                                <button type="button" onclick="VaultApp.copyToClipboard(document.getElementById('display-2fa-secret').textContent, 'Secret 2FA disalin!')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Salin Kunci">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Langkah 2: Konfirmasi Kode -->
            <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center space-x-1.5">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                    <span>Masukkan 6-Digit Kode Verifikasi dari Aplikasi</span>
                </h4>
                <div class="flex items-center space-x-3 max-w-sm">
                    <input type="text" id="confirm-2fa-code" maxlength="6" placeholder="000000" class="w-36 px-3.5 py-2 text-sm font-mono text-center tracking-widest bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                    <button type="button" onclick="confirmSetup2fa()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition whitespace-nowrap">
                        Verifikasi & Aktifkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Khusus Administrator (Akses User Manager) -->
    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 border border-blue-200 dark:border-blue-900/50 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Manajemen Pengguna & Rekan</h3>
                <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-blue-600 text-white rounded">Admin</span>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 max-w-xl">
                Tambah teman atau anggota baru, atur hak akses pengguna, serta pantau keamanan brankas seluruh akun.
            </p>
        </div>
        <a href="<?= $url('/admin/users') ?>" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition whitespace-nowrap flex items-center justify-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span>Buka User Manager</span>
        </a>
    </div>
    <?php endif; ?>

    <!-- Backup & Impor CSV -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Cadangan Data & Migrasi (Import / Export)</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unduh seluruh brankas terenkripsi atau impor data dari Bitwarden / Google Chrome CSV.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <!-- Ekspor -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-xl space-y-2">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Ekspor Cadangan</span>
                </h4>
                <p class="text-[11px] text-gray-400">Unduh seluruh item dan folder dalam file JSON terenkripsi aman.</p>
                <a href="<?= $url('/export') ?>" class="inline-block mt-2 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                    Unduh Backup JSON
                </a>
            </div>

            <!-- Impor -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-xl space-y-2">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Impor CSV</span>
                </h4>
                <p class="text-[11px] text-gray-400">Pilih berkas CSV Bitwarden / Chrome untuk dienkripsi di browser.</p>
                <label class="inline-block mt-2 px-3.5 py-1.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 text-xs font-semibold rounded-lg cursor-pointer transition">
                    <span>Pilih Berkas CSV</span>
                    <input type="file" id="csv-file-input" accept=".csv" onchange="handleCsvImport(event)" class="hidden">
                </label>
            </div>

            <!-- Emergency Kit PDF -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-xl space-y-2">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Emergency Kit PDF</span>
                </h4>
                <p class="text-[11px] text-gray-400">Lembar pemulihan fisik siap cetak berisi recovery code &amp; QR server.</p>
                <a href="<?= $url('/emergency-kit') ?>" target="_blank" class="inline-block mt-2 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                    Buka &amp; Cetak PDF
                </a>
            </div>
        </div>

        <!-- Pengaturan Keamanan Clipboard & Biometrik -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-gray-900 dark:text-white">Pembersihan Otomatis Clipboard</h4>
                <p class="text-[11px] text-gray-400">Hapus kata sandi yang disalin dari memori clipboard secara berkala.</p>
            </div>
            <div class="flex items-center space-x-2">
                <select id="setting-clipboard-timeout" onchange="localStorage.setItem('pv_clipboard_clear_seconds', this.value); AppToast.show('Pengaturan durasi clipboard diperbarui.', 'success');" class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/50">
                    <option value="15">15 Detik</option>
                    <option value="30" selected>30 Detik (Disarankan)</option>
                    <option value="60">60 Detik</option>
                    <option value="0">Jangan Pernah Bersihkan</option>
                </select>
            </div>
        </div>

        <!-- Pengaturan Buka Kunci Biometrik (Windows Hello / Touch ID) -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <span>Buka Kunci Biometrik (Windows Hello / Touch ID)</span>
                    <span id="badge-bio-status" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-400">Memeriksa sensor...</span>
                </h4>
                <p class="text-[11px] text-gray-400 mt-0.5">Gunakan sensor sidik jari atau kamera Windows Hello untuk membuka brankas tanpa mengetik Master Password.</p>
            </div>
            <div>
                <button type="button" id="btn-toggle-biometrics" onclick="toggleBiometricsSetting()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Aktifkan Biometrik
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Standalone Offline QR Code Library -->
<script src="<?= $url('/assets/js/qrcode.min.js') ?>"></script>

<script>
    let temp2faSecret = '';
    let qrCodeInstance = null;

    // Kompresi Berkas Gambar di Sisi Browser (Menjadikan foto 10MB menjadi < 50KB WebP)
    function compressImageFile(file, maxDimension = 320, quality = 0.82) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const w = img.width;
                    const h = img.height;
                    const minSide = Math.min(w, h);
                    const cropX = (w - minSide) / 2;
                    const cropY = (h - minSide) / 2;

                    const targetSize = Math.min(maxDimension, minSide);
                    canvas.width = targetSize;
                    canvas.height = targetSize;

                    const ctx = canvas.getContext('2d');
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    ctx.drawImage(img, cropX, cropY, minSide, minSide, 0, 0, targetSize, targetSize);

                    canvas.toBlob((blob) => {
                        resolve(blob || file);
                    }, 'image/webp', quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    // Unggah Foto Profil Pengguna
    async function handleAvatarUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const maxBytes = 10 * 1024 * 1024; // 10MB
        if (file.size > maxBytes) {
            AppToast.show('Ukuran berkas maksimal 10MB.', 'error');
            return;
        }

        const statusEl = document.getElementById('avatar-upload-status');
        statusEl.textContent = 'Mengompresi & mengunggah...';
        statusEl.classList.remove('hidden');

        try {
            // Kompresi di browser terlebih dahulu
            const compressedBlob = await compressImageFile(file, 320, 0.82);

            const formData = new FormData();
            formData.append('avatar', compressedBlob, 'avatar.webp');
            formData.append('csrf_token', window.PV_CONFIG.csrfToken);

            const res = await fetch(window.PV_CONFIG.routes.uploadAvatar, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: formData
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal mengunggah foto profil.');

            // Update preview di halaman
            const container = document.getElementById('avatar-preview-container');
            container.innerHTML = `<img id="avatar-img" src="${data.avatar_url}" class="w-full h-full object-cover" alt="Foto Profil">`;

            AppToast.show(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } catch (err) {
            AppToast.show(err.message, 'error');
        } finally {
            statusEl.classList.add('hidden');
        }
    }

    // Perbarui Data Profil
    async function handleProfileUpdate(e) {
        e.preventDefault();
        const username = document.getElementById('profile-username').value.trim();
        const fullName = document.getElementById('profile-fullname').value.trim();
        const phone = document.getElementById('profile-phone').value.trim();
        const btn = document.getElementById('btn-save-profile');

        if (!username) {
            AppToast.show('Username tidak boleh kosong.', 'error');
            return;
        }

        btn.disabled = true;
        try {
            const res = await fetch(window.PV_CONFIG.routes.updateProfile, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    username: username,
                    full_name: fullName,
                    phone: phone,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal memperbarui profil.');

            AppToast.show(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            AppToast.show(err.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // Setup 2FA TOTP dengan Render Barcode QR
    async function startSetup2fa() {
        const btn = document.getElementById('btn-start-2fa');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(window.PV_CONFIG.routes.setup2fa);
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            temp2faSecret = data.secret;
            document.getElementById('display-2fa-secret').textContent = data.secret;
            document.getElementById('setup-2fa-box').classList.remove('hidden');

            // Render Barcode QR Code secara offline di browser
            const qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';
            
            if (typeof QRCode !== 'undefined') {
                qrCodeInstance = new QRCode(qrContainer, {
                    text: data.otp_url,
                    width: 180,
                    height: 180,
                    colorDark: '#0b0f19',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } else {
                qrContainer.innerHTML = '<span class="text-xs text-rose-500">Gagal memuat engine barcode offline. Gunakan kunci manual di samping.</span>';
            }

            // Scroll ke wadah setup 2FA
            document.getElementById('setup-2fa-box').scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (err) {
            AppToast.show('Gagal memuat setup 2FA: ' + err.message, 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    async function confirmSetup2fa() {
        const code = document.getElementById('confirm-2fa-code').value.trim();
        if (!code || code.length !== 6) {
            AppToast.show('Masukkan kode 6 digit dari aplikasi authenticator.', 'error');
            return;
        }

        try {
            const res = await fetch(window.PV_CONFIG.routes.confirm2fa, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    secret: temp2faSecret,
                    code: code,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } catch (err) {
            AppToast.show(err.message, 'error');
        }
    }

    async function disable2fa() {
        if (!confirm('Yakin ingin menonaktifkan Autentikasi Dua Faktor (2FA)?')) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.disable2fa, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({ csrf_token: window.PV_CONFIG.csrfToken })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } catch (err) {
            AppToast.show(err.message, 'error');
        }
    }

    // CSV Parser Sederhana untuk Bitwarden & Chrome
    function handleCsvImport(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async function(evt) {
            const text = evt.target.result;
            const lines = text.split(/\r?\n/).filter(l => l.trim() !== '');
            if (lines.length < 2) {
                AppToast.show('File CSV tidak memiliki data.', 'error');
                return;
            }

            const header = lines[0].toLowerCase().split(',');
            const itemsToEncrypt = [];

            for (let i = 1; i < lines.length; i++) {
                const row = lines[i].match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g) || lines[i].split(',');
                if (row.length < 3) continue;

                const clean = row.map(v => v.replace(/^"|"$/g, '').trim());
                
                let item = {
                    name: clean[0] || 'Imported Login',
                    username: clean[1] || '',
                    password: clean[2] || '',
                    url: clean[3] || '',
                    notes: 'Diimpor dari file CSV'
                };

                itemsToEncrypt.push({
                    item_type: 'login',
                    is_favorite: 0,
                    payload: item
                });
            }

            if (itemsToEncrypt.length === 0) {
                AppToast.show('Tidak ada kredensial valid yang dapat diekstrak dari CSV.', 'error');
                return;
            }

            AppToast.show(`Mendekripsi & mengenkripsi ${itemsToEncrypt.length} item secara lokal...`, 'info');

            // Enkripsi setiap item di browser dengan Vault Key
            const encryptedBatch = [];
            for (const item of itemsToEncrypt) {
                const enc = await VaultCrypto.encryptData(item.payload, VaultApp.getVaultKey ? VaultApp.getVaultKey() : window.PV_CONFIG.activeKey);
                encryptedBatch.push({
                    item_type: item.item_type,
                    is_favorite: item.is_favorite,
                    encrypted_payload: enc.ciphertext,
                    iv: enc.iv,
                    auth_tag: enc.authTag
                });
            }

            // Kirim batch ciphertext ke server
            const res = await fetch(window.PV_CONFIG.routes.importVault, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    items: encryptedBatch,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (data.success) {
                AppToast.show(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '<?= $url('/vault') ?>';
                }, 1500);
            } else {
                AppToast.show(data.message, 'error');
            }
        };
        reader.readAsText(file);
    }

    // Pengaturan Biometrik (Windows Hello / Touch ID)
    async function updateBiometricsUI() {
        if (typeof BiometricAuth === 'undefined') return;
        const isAvail = await BiometricAuth.isAvailable();
        const isEnab = BiometricAuth.isEnabled();
        const badge = document.getElementById('badge-bio-status');
        const btn = document.getElementById('btn-toggle-biometrics');
        if (!badge || !btn) return;

        if (!isAvail) {
            badge.textContent = 'Tidak Didukung di Perangkat Ini';
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-400';
            btn.disabled = true;
            btn.textContent = 'Perangkat Tidak Mendukung';
            btn.className = 'px-3.5 py-1.5 bg-gray-300 dark:bg-gray-700 text-gray-500 rounded-xl text-xs font-semibold cursor-not-allowed';
            return;
        }

        if (isEnab) {
            badge.textContent = 'Aktif di Peramban Ini';
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400';
            btn.textContent = 'Nonaktifkan Biometrik';
            btn.className = 'px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs transition';
        } else {
            badge.textContent = 'Tersedia (Belum Aktif)';
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400';
            btn.textContent = 'Aktifkan Biometrik';
            btn.className = 'px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition';
        }
    }

    async function toggleBiometricsSetting() {
        if (typeof BiometricAuth === 'undefined') return;

        if (BiometricAuth.isEnabled()) {
            BiometricAuth.disableBiometrics();
            AppToast.show('Buka kunci biometrik telah dinonaktifkan.', 'info');
            updateBiometricsUI();
        } else {
            try {
                const vaultKey = VaultApp.getVaultKey ? VaultApp.getVaultKey() : null;
                if (!vaultKey) {
                    AppToast.show('Buka kunci brankas terlebih dahulu sebelum mengaktifkan biometrik.', 'error');
                    return;
                }
                await BiometricAuth.registerBiometrics(vaultKey);
                AppToast.show('Windows Hello / Sensor Biometrik berhasil didaftarkan!', 'success');
                updateBiometricsUI();
            } catch (err) {
                AppToast.show(err.message, 'error');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inisialisasi status clipboard setting
        const cbSel = document.getElementById('setting-clipboard-timeout');
        if (cbSel) {
            cbSel.value = localStorage.getItem('pv_clipboard_clear_seconds') || '30';
        }
        updateBiometricsUI();
    });
</script>
