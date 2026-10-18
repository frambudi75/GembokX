<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Rahasia Terenkripsi — GembokX</title>
    <link rel="icon" type="image/svg+xml" href="<?= $url('/assets/img/favicon.svg') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="max-w-lg w-full bg-gray-900 border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto border border-rose-500/20 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">Pesan Rahasia Sekali Pakai</h1>
            <p class="text-xs text-rose-400 font-medium">Burn-After-Reading • Kunci End-to-End Zero-Knowledge</p>
        </div>

        <!-- Status / Loading -->
        <div id="secret-status-loading" class="p-6 text-center text-xs text-gray-400 space-y-2">
            <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <p>Mendekripsi data rahasia di browser Anda...</p>
        </div>

        <!-- Error Box -->
        <div id="secret-error-box" class="hidden p-5 bg-rose-950/40 border border-rose-900/60 rounded-2xl text-center space-y-2">
            <span class="text-sm font-bold text-rose-400 block">Tautan Sudah Tidak Tersedia</span>
            <p id="secret-error-msg" class="text-xs text-gray-400 leading-relaxed"></p>
            <div class="pt-2">
                <a href="<?= $url('/login') ?>" class="inline-block px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-xl transition">Buka PassVault</a>
            </div>
        </div>

        <!-- Content Decrypted Box -->
        <div id="secret-content-box" class="hidden space-y-5">
            <div class="p-4 bg-amber-950/30 border border-amber-900/40 rounded-2xl text-amber-300 text-xs flex items-center space-x-2">
                <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Pesan ini telah <strong>dihapus permanen dari server</strong>. Salin data sekarang karena tautan tidak akan bisa dibuka lagi.</span>
            </div>

            <div class="p-5 bg-gray-800/60 border border-gray-700/60 rounded-2xl space-y-4 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-1">Judul / Layanan</span>
                    <span id="secret-name" class="text-base font-bold text-white block"></span>
                </div>

                <div id="box-secret-username">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-1">Nama Pengguna / Email</span>
                    <div class="flex items-center justify-between bg-gray-900 px-3.5 py-2.5 rounded-xl border border-gray-700/60">
                        <span id="secret-username" class="font-mono text-gray-200 select-all"></span>
                        <button type="button" onclick="copySecretField('secret-username', 'Username disalin!')" class="text-gray-400 hover:text-white" title="Salin">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        </button>
                    </div>
                </div>

                <div id="box-secret-password">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-1">Kata Sandi Rahasia</span>
                    <div class="flex items-center justify-between bg-gray-900 px-3.5 py-2.5 rounded-xl border border-gray-700/60">
                        <span id="secret-password" class="font-mono text-emerald-400 font-bold select-all tracking-wider"></span>
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="togglePasswordMask()" class="text-gray-400 hover:text-white" title="Sembunyikan/Tampilkan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <button type="button" onclick="copySecretField('secret-password', 'Kata sandi disalin!')" class="text-gray-400 hover:text-emerald-400" title="Salin">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="box-secret-notes" class="hidden">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-1">Catatan Tambahan</span>
                    <div id="secret-notes" class="p-3 bg-gray-900 rounded-xl border border-gray-700/60 font-mono text-gray-300 text-[11px] whitespace-pre-wrap select-all"></div>
                </div>
            </div>

            <div class="text-center pt-2">
                <a href="<?= $url('/') ?>" class="text-xs text-blue-400 hover:underline">Gunakan PassVault Password Manager</a>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="secret-toast" class="fixed bottom-5 right-5 z-50 hidden bg-gray-800 text-white border border-gray-700 rounded-xl px-4 py-2 text-xs shadow-xl font-medium"></div>

    <script src="<?= $url('/assets/js/crypto.js') ?>"></script>
    <script>
        let isMasked = false;
        let originalPass = '';

        function showToast(msg) {
            const toast = document.getElementById('secret-toast');
            toast.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2500);
        }

        function copySecretField(id, successMsg) {
            const val = id === 'secret-password' ? originalPass : document.getElementById(id).textContent;
            navigator.clipboard.writeText(val).then(() => {
                showToast(successMsg + ' (Riwayat clipboard akan dibersihkan otomatis)');
                setTimeout(() => {
                    navigator.clipboard.writeText('').catch(() => {});
                }, 30000);
            });
        }

        function togglePasswordMask() {
            const el = document.getElementById('secret-password');
            if (isMasked) {
                el.textContent = originalPass;
                isMasked = false;
            } else {
                el.textContent = '•'.repeat(Math.min(originalPass.length, 16));
                isMasked = true;
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const token = '<?= $e($token) ?>';
            const hash = window.location.hash.replace(/^#/, '');

            if (!hash) {
                document.getElementById('secret-status-loading').classList.add('hidden');
                document.getElementById('secret-error-msg').textContent = 'Kunci dekripsi di fragment URL (#) tidak ditemukan atau terpotong.';
                document.getElementById('secret-error-box').classList.remove('hidden');
                return;
            }

            try {
                // Ambil ciphertext dari server (sekaligus menghanguskan record dari server)
                const res = await fetch('<?= $url('/api/secret/consume/') ?>' + token, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });

                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'Tautan ini sudah hangus atau kadaluarsa.');
                }

                // Import symmetric AES key dari URL fragment (#)
                const rawKey = VaultCrypto.base64ToBuffer(hash);
                const key = await window.crypto.subtle.importKey(
                    'raw',
                    rawKey,
                    { name: 'AES-GCM', length: 256 },
                    false,
                    ['decrypt']
                );

                // Dekripsi di peramban
                const payload = await VaultCrypto.decryptData(data.encrypted_payload, data.iv, key);
                if (!payload) throw new Error('Kunci enkripsi tidak cocok untuk membuka data ini.');

                document.getElementById('secret-name').textContent = payload.name || 'Informasi Rahasia';

                if (payload.username) {
                    document.getElementById('secret-username').textContent = payload.username;
                } else {
                    document.getElementById('box-secret-username').classList.add('hidden');
                }

                if (payload.password) {
                    originalPass = payload.password;
                    document.getElementById('secret-password').textContent = payload.password;
                } else {
                    document.getElementById('box-secret-password').classList.add('hidden');
                }

                if (payload.notes) {
                    document.getElementById('secret-notes').textContent = payload.notes;
                    document.getElementById('box-secret-notes').classList.remove('hidden');
                }

                document.getElementById('secret-status-loading').classList.add('hidden');
                document.getElementById('secret-content-box').classList.remove('hidden');

            } catch (err) {
                document.getElementById('secret-status-loading').classList.add('hidden');
                document.getElementById('secret-error-msg').textContent = err.message;
                document.getElementById('secret-error-box').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
