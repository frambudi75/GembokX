<!-- Form Register Zero-Knowledge -->
<form id="register-form" onsubmit="handleRegisterSubmit(event)" class="space-y-4">
    <div>
        <label for="reg-username" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Pengguna / Username</label>
        <input type="text" id="reg-username" required autofocus placeholder="habib" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
    </div>

    <div>
        <label for="reg-email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Alamat Email</label>
        <input type="email" id="reg-email" required placeholder="user@example.com" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="reg-master-pwd" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Master Password</label>
            <span class="text-[11px] text-gray-400">Min. 10 karakter</span>
        </div>
        <div class="relative">
            <input type="password" id="reg-master-pwd" required oninput="checkRegisterPwdStrength(this.value)" placeholder="••••••••••••" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white pr-10">
            <button type="button" onclick="togglePasswordVisibility('reg-master-pwd')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </button>
        </div>
        <!-- Strength Bar -->
        <div class="mt-2 flex items-center space-x-2">
            <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div id="reg-strength-bar" class="h-full bg-gray-400 w-0 transition-all duration-300"></div>
            </div>
            <span id="reg-strength-text" class="text-[11px] font-medium text-gray-400">Kosong</span>
        </div>
    </div>

    <div>
        <label for="reg-confirm-pwd" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Konfirmasi Master Password</label>
        <input type="password" id="reg-confirm-pwd" required placeholder="••••••••••••" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
    </div>

    <div id="reg-status-msg" class="hidden text-xs text-center py-2 px-3 rounded-lg border"></div>

    <button type="submit" id="reg-submit-btn" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center justify-center space-x-2">
        <span>Buat Brankas Saya</span>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-800 text-center">
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Sudah memiliki akun brankas? 
        <a href="<?= $url('/login') ?>" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">Masuk di sini</a>
    </p>
</div>

<!-- Modal Kunci Pemulihan Darurat (Emergency Recovery Key) -->
<div id="recovery-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl text-center">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Simpan Kode Pemulihan Darurat</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">
            Jika Anda lupa Master Password, kode ini adalah <strong>satu-satunya cara</strong> untuk memulihkan akses ke akun Anda.
        </p>

        <div class="p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl mb-4">
            <span id="display-recovery-code" class="text-base font-mono font-bold tracking-wider text-blue-600 dark:text-blue-400 select-all"></span>
        </div>

        <button type="button" onclick="copyRecoveryCodeAndProceed()" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition mb-2">
            Salin Kode & Lanjut ke Brankas
        </button>
        <button type="button" onclick="window.location.href = window.targetVaultRedirect;" class="text-xs text-gray-400 hover:underline">
            Saya sudah mencatatnya, langsung buka brankas
        </button>
    </div>
</div>

<script>
    let generatedRecoveryCode = '';
    window.targetVaultRedirect = '<?= $url('/vault') ?>';

    function togglePasswordVisibility(inputId) {
        const el = document.getElementById(inputId);
        if (el) el.type = el.type === 'password' ? 'text' : 'password';
    }

    function checkRegisterPwdStrength(pwd) {
        const bar = document.getElementById('reg-strength-bar');
        const text = document.getElementById('reg-strength-text');
        const res = PasswordGenerator.evaluateStrength(pwd);

        bar.className = `h-full transition-all duration-300 ${res.color}`;
        bar.style.width = res.width;
        text.textContent = res.label;
        text.className = `text-[11px] font-medium ${res.text || 'text-gray-400'}`;
    }

    async function handleRegisterSubmit(e) {
        e.preventDefault();
        const username = document.getElementById('reg-username').value.trim();
        const email = document.getElementById('reg-email').value.trim().toLowerCase();
        const pwd = document.getElementById('reg-master-pwd').value;
        const confirmPwd = document.getElementById('reg-confirm-pwd').value;
        const statusMsg = document.getElementById('reg-status-msg');
        const btn = document.getElementById('reg-submit-btn');

        if (pwd.length < 10) {
            statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-rose-50 dark:bg-rose-950/40 text-rose-500 border-rose-200 dark:border-rose-900/50';
            statusMsg.textContent = 'Master password minimal 10 karakter untuk keamanan optimal.';
            statusMsg.classList.remove('hidden');
            return;
        }

        if (pwd !== confirmPwd) {
            statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-rose-50 dark:bg-rose-950/40 text-rose-500 border-rose-200 dark:border-rose-900/50';
            statusMsg.textContent = 'Konfirmasi password tidak cocok.';
            statusMsg.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        statusMsg.textContent = 'Menghasilkan parameter kriptografi (PBKDF2 600.000 iterasi)...';
        statusMsg.classList.remove('hidden');

        try {
            // 1. Generate Salt
            const kdfSalt = VaultCrypto.generateSalt(32);
            const kdfIterations = 600000;

            // 2. Derivasi Master Key
            const masterKey = await VaultCrypto.deriveMasterKey(pwd, kdfSalt, kdfIterations);
            
            // 3. Derivasi Auth Key untuk server
            const authKey = await VaultCrypto.deriveAuthKey(masterKey);

            // 4. Generate Kunci Simetris Brankas (Vault Key)
            const vaultKey = await VaultCrypto.generateVaultKey();

            // 5. Enkripsi Vault Key dengan Master Key
            const encVaultKeyResult = await VaultCrypto.encryptVaultKey(vaultKey, masterKey);

            // 6. Generate Emergency Recovery Code
            generatedRecoveryCode = VaultCrypto.generateRecoveryCode();

            statusMsg.textContent = 'Mendaftarkan akun ke server...';

            // 7. Kirim data registrasi ke server
            const res = await fetch(window.PV_CONFIG.routes.register, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    email: email,
                    username: username,
                    auth_key: authKey,
                    kdf_salt: kdfSalt,
                    kdf_iterations: kdfIterations,
                    encrypted_vault_key: encVaultKeyResult.encryptedKey,
                    vault_key_iv: encVaultKeyResult.iv,
                    recovery_code: generatedRecoveryCode,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            let data;
            const resText = await res.text();
            try {
                data = JSON.parse(resText);
            } catch (e) {
                if (res.status === 503) {
                    throw new Error('Layanan database sedang melakukan perbaikan skema. Silakan coba sesaat lagi.');
                }
                throw new Error('Gagal memproses respon server (' + res.status + '). Silakan coba sesaat lagi.');
            }

            if (!data.success) throw new Error(data.message);

            // Tampilkan Modal Recovery Code
            document.getElementById('display-recovery-code').textContent = generatedRecoveryCode;
            window.targetVaultRedirect = data.redirect;
            document.getElementById('recovery-modal').classList.remove('hidden');
            document.getElementById('recovery-modal').classList.add('flex');
        } catch (err) {
            statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-rose-50 dark:bg-rose-950/40 text-rose-500 border-rose-200 dark:border-rose-900/50';
            statusMsg.textContent = err.message || 'Gagal mendaftar akun.';
            btn.disabled = false;
        }
    }

    async function copyRecoveryCodeAndProceed() {
        try {
            await navigator.clipboard.writeText(generatedRecoveryCode);
            AppToast.show('Kode pemulihan disalin!', 'success');
        } catch (e) {}
        setTimeout(() => {
            window.location.href = window.targetVaultRedirect;
        }, 1000);
    }
</script>
