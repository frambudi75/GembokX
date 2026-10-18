<!-- Modal Standalone Generator Kata Sandi -->
<div id="generator-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center space-x-2.5">
                <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Generator Kata Sandi Kuat</h3>
            </div>
            <button type="button" onclick="closeModal('generator-modal')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="mt-5 space-y-4">
            <!-- Output Box -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-2">
                    <div id="gen-output-text" class="text-base font-mono font-medium text-gray-900 dark:text-white break-all select-all flex-1">
                        <!-- Password ter-generate -->
                    </div>
                    <div class="flex items-center space-x-1 flex-shrink-0">
                        <button type="button" onclick="runPasswordGenerator()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Buat Ulang">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                        <button type="button" onclick="copyGeneratedPassword()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                <!-- Strength Indicator -->
                <div class="mt-3 flex items-center space-x-2">
                    <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div id="gen-strength-bar" class="h-full bg-emerald-500 w-full transition-all duration-300"></div>
                    </div>
                    <span id="gen-strength-label" class="text-xs font-medium text-emerald-500">Sangat Kuat</span>
                </div>
            </div>

            <!-- Mode Selector -->
            <div class="flex border-b border-gray-200 dark:border-gray-800 pb-2">
                <button type="button" onclick="setGeneratorMode('password')" id="gen-mode-password-btn" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 transition mr-2">Kata Sandi Acak</button>
                <button type="button" onclick="setGeneratorMode('passphrase')" id="gen-mode-passphrase-btn" class="px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg transition">Passphrase (Diceware)</button>
            </div>

            <!-- Opsi Password Mode -->
            <div id="gen-options-password" class="space-y-3.5">
                <div>
                    <div class="flex justify-between text-xs font-medium mb-1.5">
                        <span class="text-gray-700 dark:text-gray-300">Panjang Karakter: <strong id="gen-length-val" class="text-blue-600 dark:text-blue-400 font-mono">20</strong></span>
                    </div>
                    <input type="range" id="gen-length-slider" min="8" max="64" value="20" oninput="document.getElementById('gen-length-val').textContent = this.value; runPasswordGenerator();" class="w-full accent-blue-600 cursor-pointer">
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg cursor-pointer">
                        <input type="checkbox" id="gen-opt-uppercase" checked onchange="runPasswordGenerator()" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700 dark:text-gray-300">Huruf Besar (A-Z)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg cursor-pointer">
                        <input type="checkbox" id="gen-opt-lowercase" checked onchange="runPasswordGenerator()" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700 dark:text-gray-300">Huruf Kecil (a-z)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg cursor-pointer">
                        <input type="checkbox" id="gen-opt-numbers" checked onchange="runPasswordGenerator()" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700 dark:text-gray-300">Angka (0-9)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg cursor-pointer">
                        <input type="checkbox" id="gen-opt-symbols" checked onchange="runPasswordGenerator()" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700 dark:text-gray-300">Simbol Khusus (!@#$)</span>
                    </label>
                </div>
            </div>

            <!-- Opsi Passphrase Mode -->
            <div id="gen-options-passphrase" class="space-y-3 hidden">
                <div>
                    <div class="flex justify-between text-xs font-medium mb-1.5">
                        <span class="text-gray-700 dark:text-gray-300">Jumlah Kata: <strong id="gen-words-val" class="text-blue-600 dark:text-blue-400 font-mono">4</strong></span>
                    </div>
                    <input type="range" id="gen-words-slider" min="3" max="8" value="4" oninput="document.getElementById('gen-words-val').textContent = this.value; runPasswordGenerator();" class="w-full accent-blue-600 cursor-pointer">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeGenMode = 'password';

    function setGeneratorMode(mode) {
        activeGenMode = mode;
        const pwdBtn = document.getElementById('gen-mode-password-btn');
        const passBtn = document.getElementById('gen-mode-passphrase-btn');
        const optPwd = document.getElementById('gen-options-password');
        const optPass = document.getElementById('gen-options-passphrase');

        if (mode === 'password') {
            pwdBtn.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 transition mr-2';
            passBtn.className = 'px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg transition';
            optPwd.classList.remove('hidden');
            optPass.classList.add('hidden');
        } else {
            passBtn.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 transition';
            pwdBtn.className = 'px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg transition mr-2';
            optPass.classList.remove('hidden');
            optPwd.classList.add('hidden');
        }

        runPasswordGenerator();
    }

    function runPasswordGenerator() {
        let result = '';
        if (activeGenMode === 'password') {
            const length = parseInt(document.getElementById('gen-length-slider').value) || 20;
            const useUppercase = document.getElementById('gen-opt-uppercase').checked;
            const useLowercase = document.getElementById('gen-opt-lowercase').checked;
            const useNumbers = document.getElementById('gen-opt-numbers').checked;
            const useSymbols = document.getElementById('gen-opt-symbols').checked;

            result = PasswordGenerator.generatePassword({
                length,
                useUppercase,
                useLowercase,
                useNumbers,
                useSymbols,
                avoidAmbiguous: true
            });
        } else {
            const numWords = parseInt(document.getElementById('gen-words-slider').value) || 4;
            result = PasswordGenerator.generatePassphrase(numWords, '-');
        }

        document.getElementById('gen-output-text').textContent = result;

        // Update strength
        const strength = PasswordGenerator.evaluateStrength(result);
        const bar = document.getElementById('gen-strength-bar');
        const label = document.getElementById('gen-strength-label');

        bar.className = `h-full transition-all duration-300 ${strength.color}`;
        bar.style.width = strength.width;
        label.textContent = strength.label;
        label.className = `text-xs font-medium ${strength.text}`;
    }

    function copyGeneratedPassword() {
        const pwd = document.getElementById('gen-output-text').textContent;
        VaultApp.copyToClipboard(pwd, 'Kata sandi acak disalin!');
    }

    document.addEventListener('DOMContentLoaded', () => {
        runPasswordGenerator();
    });
</script>
