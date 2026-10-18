<!-- Modal Buka Kunci Brankas (Unlock Vault) -->
<div id="unlock-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl relative">
        <div class="text-center mb-6">
            <div class="inline-flex w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Buka Kunci Brankas Anda</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Masukkan Master Password Anda untuk mendekripsi brankas di browser ini.</p>
        </div>

        <form id="unlock-form" onsubmit="handleUnlockSubmit(event)" class="space-y-4">
            <div>
                <label for="unlock-master-password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Master Password</label>
                <div class="relative">
                    <input type="password" id="unlock-master-password" required autofocus placeholder="Masukkan master password..." class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    <button type="button" onclick="togglePasswordVisibility('unlock-master-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
            </div>

            <div id="unlock-error" class="hidden text-xs text-rose-500 font-medium p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-lg"></div>

            <button type="submit" id="unlock-submit-btn" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center justify-center space-x-2">
                <span>Buka Brankas</span>
            </button>
        </form>

        <!-- Tombol Buka Cepat dengan Biometrik / Windows Hello (Jika diaktifkan) -->
        <div id="biometric-unlock-box" class="hidden pt-3 border-t border-gray-100 dark:border-gray-800">
            <button type="button" onclick="unlockWithBiometrics()" id="btn-biometric-unlock" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                <span>Buka Cepat dengan Windows Hello / Sidik Jari</span>
            </button>
        </div>

        <div class="mt-4 text-center">
            <a href="<?= $url('/logout') ?>" class="text-xs text-gray-400 hover:text-rose-500 transition">Keluar dari akun</a>
        </div>
    </div>
</div>

<script>
    function showLockModal() {
        const modal = document.getElementById('unlock-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const pwd = document.getElementById('unlock-master-password');
            if (pwd) {
                pwd.value = '';
                pwd.focus();
            }

            // Tampilkan tombol biometrik jika sudah diaktifkan di peramban ini
            if (typeof BiometricAuth !== 'undefined' && BiometricAuth.isEnabled()) {
                document.getElementById('biometric-unlock-box')?.classList.remove('hidden');
            }
        }
    }

    async function unlockWithBiometrics() {
        const errDiv = document.getElementById('unlock-error');
        errDiv.classList.add('hidden');
        try {
            const vaultKey = await BiometricAuth.authenticateBiometrics();
            if (vaultKey) {
                await VaultApp.setDirectVaultKey(vaultKey);
                closeModal('unlock-modal');
                await VaultApp.syncAndDecrypt();
                AppToast.show('Brankas berhasil dibuka dengan Biometrik!', 'success');
            }
        } catch (err) {
            errDiv.textContent = 'Verifikasi biometrik gagal: ' + err.message;
            errDiv.classList.remove('hidden');
        }
    }

    async function handleUnlockSubmit(e) {
        e.preventDefault();
        const pwdInput = document.getElementById('unlock-master-password');
        const errDiv = document.getElementById('unlock-error');
        const btn = document.getElementById('unlock-submit-btn');

        const masterPwd = pwdInput.value;
        if (!masterPwd) return;

        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> <span>Mendekripsi...</span>`;
        errDiv.classList.add('hidden');

        try {
            const meta = window.PV_CONFIG.vaultKeyMeta;
            const success = await VaultApp.unlockWithMasterPassword(
                masterPwd,
                meta.encrypted,
                meta.iv,
                meta.salt,
                meta.iterations,
                meta.algorithm || 'pbkdf2',
                meta.memory || 65536,
                meta.parallelism || 4
            );

            if (success) {
                closeModal('unlock-modal');
                await VaultApp.syncAndDecrypt();
                AppToast.show('Brankas berhasil dibuka!', 'success');
            } else {
                errDiv.textContent = 'Master Password salah atau tidak cocok untuk brankas ini.';
                errDiv.classList.remove('hidden');
            }
        } catch (err) {
            errDiv.textContent = 'Terjadi kesalahan saat mendekripsi: ' + err.message;
            errDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Buka Brankas</span>';
        }
    }

    function togglePasswordVisibility(inputId) {
        const el = document.getElementById(inputId);
        if (el) {
            el.type = el.type === 'password' ? 'text' : 'password';
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        VaultApp.setupActivityListeners();

        // 1. Coba pulihkan kunci enkripsi dari tab browser jika sesi masih aktif
        const isRestored = await VaultApp.tryRestoreSession();
        if (isRestored) {
            await VaultApp.syncAndDecrypt();
            return;
        }

        // 2. Hanya wajibkan modal buka kunci jika berada di halaman brankas (/vault) atau audit (/audit)
        const currentPath = window.location.pathname;
        const needsVaultUnlock = currentPath.endsWith('/vault') || 
                                 currentPath.endsWith('/vault/') || 
                                 currentPath.endsWith('/audit') || 
                                 document.getElementById('vault-items-container') !== null;

        if (needsVaultUnlock) {
            showLockModal();
        }
    });
</script>
