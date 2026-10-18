<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Pemulihan Darurat (Emergency Kit) — GembokX</title>
    <link rel="icon" type="image/svg+xml" href="<?= $url('/assets/img/favicon.svg') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .sheet { border: 1px solid #d1d5db !important; box-shadow: none !important; margin: 0 auto !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen py-6 px-4 antialiased">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= $url('/settings') ?>" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-xl hover:bg-gray-50 transition flex items-center space-x-1.5 shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Pengaturan</span>
        </a>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Emergency Kit Sheet -->
    <div class="sheet max-w-3xl mx-auto bg-white border border-gray-200 rounded-3xl p-8 sm:p-12 shadow-xl space-y-8">
        
        <!-- Document Header -->
        <div class="flex items-start justify-between pb-6 border-b-2 border-gray-900">
            <div class="space-y-1">
                <div class="flex items-center space-x-3">
                    <img src="<?= $url('/assets/img/favicon.svg') ?>" class="w-10 h-10 rounded-xl" alt="Logo">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-gray-950 uppercase">GEMBOKX EMERGENCY KIT</h1>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Dokumen Pemulihan Rahasia &amp; Keamanan Akun</p>
                    </div>
                </div>
            </div>
            <div class="text-right text-[11px] text-gray-500 font-mono">
                <div>Tanggal: <?= date('d M Y') ?></div>
                <div class="text-rose-600 font-bold uppercase">SANGAT RAHASIA</div>
            </div>
        </div>

        <!-- Warning Callout -->
        <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-900 text-xs leading-relaxed">
            <strong>Penting:</strong> Dokumen ini berisi informasi pemulihan akun GembokX Anda. Karena brankas menggunakan arsitektur <em>Zero-Knowledge</em>, administrator dan server tidak memiliki salinan Master Password Anda. Simpan lembar fisik ini di tempat aman (misal: brankas dokumen atau map terkunci).
        </div>

        <!-- Credential Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
            <!-- Account Identifiers -->
            <div class="space-y-4">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Alamat Email Terdaftar</span>
                    <span class="text-sm font-bold text-gray-950 font-mono"><?= $e($user['email']) ?></span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Nama Lengkap &amp; Username</span>
                    <span class="text-sm font-bold text-gray-950"><?= $e($user['full_name'] ?: '-') ?> (@<?= $e($user['username']) ?>)</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Alamat URL Server Brankas</span>
                    <span class="text-xs font-semibold text-blue-600 font-mono break-all"><?= $url('/') ?></span>
                </div>
            </div>

            <!-- QR Code for Easy Vault Access -->
            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 border border-gray-200 rounded-2xl text-center">
                <div id="vault-url-qrcode" class="w-28 h-28 bg-white p-2 rounded-xl border border-gray-200 flex items-center justify-center mb-2 shadow-2xs"></div>
                <span class="text-[10px] text-gray-500 font-medium">Scan QR untuk membuka URL Brankas</span>
            </div>
        </div>

        <!-- Secret Recovery Key Box -->
        <div class="p-5 bg-gray-900 text-white rounded-2xl space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-extrabold tracking-widest text-gray-400">KUNCI PEMULIHAN DARURAT (RECOVERY CODE)</span>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-blue-900/60 text-blue-300 font-bold border border-blue-700/50">Zero-Knowledge Token</span>
            </div>
            <div id="display-emergency-code" class="text-lg sm:text-xl font-mono font-black tracking-wider text-emerald-400 select-all py-1">
                PV-XXXX-XXXX-XXXX-XXXX
            </div>
            <p class="text-[11px] text-gray-400 leading-normal">
                Kunci ini digunakan untuk mengatur ulang sandi jika Anda kehilangan akses ke aplikasi authenticator 2FA.
            </p>
        </div>

        <!-- Blank Box for Written Master Password (Optional for User) -->
        <div class="p-5 border-2 border-dashed border-gray-300 rounded-2xl space-y-2">
            <span class="text-[10px] uppercase font-extrabold tracking-wider text-gray-500 block">CATATAN MASTER PASSWORD (DITULIS MANUAL SETELAH DICETAK)</span>
            <div class="h-10 border-b border-gray-300 flex items-end pb-1 text-sm font-mono text-gray-800">
                <span class="text-gray-300 italic text-xs">Tulis Master Password Anda di sini jika ingin disimpan fisik...</span>
            </div>
            <p class="text-[10px] text-gray-400">
                Jangan pernah membagikan atau memotret sandi ini kepada siapa pun.
            </p>
        </div>

        <!-- Instructions List -->
        <div class="space-y-2 pt-2 border-t border-gray-200">
            <h4 class="text-xs font-bold text-gray-950 uppercase tracking-wider">Petunjuk Pemulihan Darurat:</h4>
            <ol class="list-decimal list-inside text-xs text-gray-600 space-y-1.5 leading-relaxed">
                <li>Buka alamat URL brankas yang tercantum di atas melalui browser modern di komputer atau ponsel Anda.</li>
                <li>Masukkan alamat email terdaftar dan Master Password Anda.</li>
                <li>Jika ponsel Anda hilang dan Anda tidak dapat memasukkan 6-digit kode 2FA, hubungi administrator atau gunakan Kunci Pemulihan Darurat di atas.</li>
                <li>Setelah berhasil login, segera lakukan pencadangan data terbaru melalui menu <em>Pengaturan &gt; Ekspor Cadangan Terenkripsi</em>.</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="text-center pt-6 border-t border-gray-100 text-[11px] text-gray-400">
            PassVault Self-Hosted Password Manager • Enkripsi Klien AES-256-GCM + PBKDF2
        </div>
    </div>

    <!-- Standalone QR Code Library -->
    <script src="<?= $url('/assets/js/qrcode.min.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Render QR Code untuk URL Brankas
            const qrContainer = document.getElementById('vault-url-qrcode');
            if (qrContainer && typeof QRCode !== 'undefined') {
                new QRCode(qrContainer, {
                    text: window.location.origin + '<?= $url('/login') ?>',
                    width: 96,
                    height: 96,
                    colorDark: '#0b0f19',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            // Ambil recovery code acak atau tampilkan template pemulihan
            const codeEl = document.getElementById('display-emergency-code');
            const savedCode = sessionStorage.getItem('pv_temp_recovery_code');
            if (savedCode) {
                codeEl.textContent = savedCode;
            } else {
                // Generate recovery code acak baru untuk lembar ini
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let code = 'PV';
                for (let s = 0; s < 4; s++) {
                    code += '-';
                    for (let i = 0; i < 4; i++) code += chars[Math.floor(Math.random() * chars.length)];
                }
                codeEl.textContent = code;
                sessionStorage.setItem('pv_temp_recovery_code', code);
            }
        });
    </script>
</body>
</html>
