<!-- Modal Tambah Folder Baru -->
<div id="folder-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-sm w-full p-5 sm:p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tambah Folder Baru</h3>
            <button type="button" onclick="closeModal('folder-modal')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="folder-form" onsubmit="handleFolderFormSubmit(event)" class="mt-4 space-y-4">
            <div>
                <label for="new-folder-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Folder *</label>
                <input type="text" id="new-folder-name" required placeholder="misal: Pekerjaan, Medsos, Bank" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('folder-modal')" class="px-3.5 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition">Batal</button>
                <button type="submit" id="folder-submit-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">Buat Folder</button>
            </div>
        </form>
    </div>
</div>

<script>
    async function handleFolderFormSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('new-folder-name');
        const btn = document.getElementById('folder-submit-btn');
        const name = input.value.trim();
        if (!name) return;

        btn.disabled = true;
        try {
            // Enkripsi nama folder dengan Vault Key
            const encrypted = await VaultCrypto.encryptData(name, VaultApp.getVaultKey ? VaultApp.getVaultKey() : window.PV_CONFIG.activeKey);
            
            // Simpan ke API
            const res = await fetch(window.PV_CONFIG.routes.saveFolder, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    encrypted_name: encrypted.ciphertext,
                    iv: encrypted.iv,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            input.value = '';
            closeModal('folder-modal');
            await VaultApp.syncAndDecrypt();
        } catch (err) {
            AppToast.show('Gagal membuat folder: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }
</script>
