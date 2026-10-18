<!-- Modal Tautan Rahasia Sekali Pakai (Burn-After-Reading) -->
<div id="modal-share-secret" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center space-x-2">
                <span class="p-1.5 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </span>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tautan Rahasia Sekali Pakai</h3>
            </div>
            <button type="button" onclick="closeModal('modal-share-secret')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
            Kredensial ini telah dienkripsi secara <em>End-to-End</em>. Kunci dekripsi berada di bagian URL hash (<code class="text-purple-500">#key</code>) dan <strong>tidak pernah disimpan di server</strong>.
        </p>

        <div class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 rounded-xl text-rose-700 dark:text-rose-300 text-[11px] flex items-center space-x-2">
            <svg class="w-4 h-4 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span><strong>Burn-After-Reading:</strong> Tautan hanya dapat dibuka tepat 1 kali, lalu otomatis musnah dari database.</span>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Tautan Rahasia (Salin &amp; Kirim ke Teman):</label>
            <div class="relative">
                <input type="text" id="share-secret-url-input" readonly class="w-full pl-3 pr-10 py-2.5 text-xs font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl select-all text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/50">
                <button type="button" onclick="copySecretShareUrl()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-purple-600 dark:hover:text-purple-400" title="Salin Tautan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                </button>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-end space-x-2">
            <button type="button" onclick="closeModal('modal-share-secret')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Tutup
            </button>
            <button type="button" onclick="copySecretShareUrl()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Salin Tautan</span>
            </button>
        </div>
    </div>
</div>

<script>
    function copySecretShareUrl() {
        const inp = document.getElementById('share-secret-url-input');
        if (inp && inp.value) {
            VaultApp.copyToClipboard(inp.value, 'Tautan rahasia disalin ke clipboard!');
        }
    }
</script>
