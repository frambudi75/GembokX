<!-- Form Login Zero-Knowledge -->
<form id="login-form" onsubmit="handleLoginSubmit(event)" class="space-y-4">
    <div>
        <label for="login-email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Alamat Email</label>
        <input type="email" id="login-email" required autofocus placeholder="user@example.com" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="login-master-pwd" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Master Password</label>
            <span class="text-[11px] text-gray-400">Zero-Knowledge</span>
        </div>
        <div class="relative">
            <input type="password" id="login-master-pwd" required placeholder="••••••••••••" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white pr-10">
            <button type="button" onclick="togglePasswordVisibility('login-master-pwd')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </button>
        </div>
    </div>

    <!-- Field 2FA (Muncul otomatis jika user telah mengaktifkan 2FA) -->
    <div id="totp-field-container" class="hidden">
        <label for="login-totp-code" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kode Autentikasi 2FA (TOTP)</label>
        <input type="text" id="login-totp-code" maxlength="6" placeholder="123456" class="w-full px-4 py-2.5 text-sm font-mono text-center tracking-widest bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
        <p class="text-[11px] text-gray-400 mt-1 text-center">Buka aplikasi Google Authenticator / Authy Anda.</p>
    </div>

    <div id="login-status-msg" class="hidden text-xs text-center py-2 px-3 rounded-lg border"></div>

    <button type="submit" id="login-submit-btn" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center justify-center space-x-2">
        <span>Masuk ke Brankas</span>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-800 text-center">
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Belum memiliki brankas? 
        <a href="<?= $url('/register') ?>" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">Daftar Akun Baru</a>
    </p>
</div>

<script>
    function togglePasswordVisibility(inputId) {
        const el = document.getElementById(inputId);
        if (el) el.type = el.type === 'password' ? 'text' : 'password';
    }

    async function handleLoginSubmit(e) {
        e.preventDefault();
        const emailInput = document.getElementById('login-email');
        const pwdInput = document.getElementById('login-master-pwd');
        const totpInput = document.getElementById('login-totp-code');
        const totpContainer = document.getElementById('totp-field-container');
        const statusMsg = document.getElementById('login-status-msg');
        const btn = document.getElementById('login-submit-btn');

        const email = emailInput.value.trim().toLowerCase();
        const password = pwdInput.value;
        const totpCode = totpInput.value.trim();

        if (!email || !password) return;

        btn.disabled = true;
        statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        statusMsg.textContent = 'Mendapatkan parameter kriptografi (Prelogin)...';
        statusMsg.classList.remove('hidden');

        async function parseSafeJson(res) {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                if (res.status === 503) {
                    throw new Error('Layanan database sedang bersiap atau melakukan perbaikan skema. Silakan coba sesaat lagi.');
                }
                throw new Error('Gagal memproses respon server (' + res.status + '). Silakan periksa koneksi atau coba sesaat lagi.');
            }
        }

        try {
            // 1. Ambil Salt & KDF Iterations
            const preRes = await fetch(window.PV_CONFIG.routes.prelogin, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({ email: email, csrf_token: window.PV_CONFIG.csrfToken })
            });

            const preData = await parseSafeJson(preRes);
            if (!preData.success) throw new Error(preData.message);

            if (preData.totp_required && !totpCode && totpContainer.classList.contains('hidden')) {
                totpContainer.classList.remove('hidden');
                totpInput.focus();
                statusMsg.textContent = 'Masukkan kode 2FA dari aplikasi Authenticator Anda.';
                btn.disabled = false;
                return;
            }

            const kdfAlgo = preData.kdf_algorithm || 'pbkdf2';
            statusMsg.textContent = kdfAlgo === 'argon2id' 
                ? 'Mendriver Master Key (Argon2id Memory-Hard WASM)...' 
                : 'Mendriver Master Key (600.000 iterasi PBKDF2)...';

            // 2. Derivasi Master Key dan Auth Key secara lokal di browser
            const masterKey = await VaultCrypto.deriveMasterKey(
                password, 
                preData.kdf_salt, 
                preData.kdf_iterations,
                kdfAlgo,
                preData.kdf_memory || 65536,
                preData.kdf_parallelism || 4
            );
            const authKey = await VaultCrypto.deriveAuthKey(masterKey);

            statusMsg.textContent = 'Memverifikasi kredensial...';

            // 3. Kirim Auth Key ke server (Bukan Master Password)
            const loginRes = await fetch(window.PV_CONFIG.routes.login, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    email: email,
                    auth_key: authKey,
                    totp_code: totpCode,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const loginData = await parseSafeJson(loginRes);
            if (!loginData.success) {
                if (loginData.totp_required) {
                    totpContainer.classList.remove('hidden');
                    totpInput.focus();
                }
                throw new Error(loginData.message);
            }

            statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
            statusMsg.textContent = 'Login berhasil! Membuka brankas...';

            // Simpan master key di RAM untuk sesi ini
            window.location.href = loginData.redirect;
        } catch (err) {
            statusMsg.className = 'text-xs text-center py-2 px-3 rounded-lg border bg-rose-50 dark:bg-rose-950/40 text-rose-500 border-rose-200 dark:border-rose-900/50';
            statusMsg.textContent = err.message || 'Gagal masuk ke akun.';
            btn.disabled = false;
        }
    }
</script>
