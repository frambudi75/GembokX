<!-- Modal Detail Item Brankas -->
<div id="detail-modal" class="modal-backdrop fixed inset-0 z-40 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl relative my-auto sm:my-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-start justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
            <div class="min-w-0 pr-4">
                <div class="flex items-center space-x-2">
                    <span id="detail-type-badge" class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">Login</span>
                    <span id="detail-folder-badge" class="px-2 py-0.5 text-[10px] rounded bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Folder</span>
                </div>
                <h3 id="detail-name" class="text-lg font-bold text-gray-900 dark:text-white truncate mt-1">Nama Item</h3>
            </div>
            <div class="flex items-center space-x-1">
                <button type="button" id="detail-fav-btn" onclick="VaultApp.handleDetailToggleFav()" class="p-2 text-gray-400 hover:text-amber-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </button>
                <button type="button" onclick="closeModal('detail-modal')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Body Detail Fields -->
        <div id="detail-fields-body" class="mt-4 space-y-3.5">
            <!-- Diisi secara dinamis melalui JavaScript -->
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center space-x-2">
                <button type="button" id="detail-delete-btn" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 dark:border-rose-900/60">
                    Hapus
                </button>
                <button type="button" onclick="VaultApp.createSecretShareLink()" class="px-3 py-2 text-xs font-semibold text-purple-600 hover:text-white hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white rounded-xl transition border border-purple-200 dark:border-purple-900/60 flex items-center space-x-1" title="Buat Tautan Rahasia Sekali Pakai">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                    <span>Bagikan</span>
                </button>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="closeModal('detail-modal')" class="px-4 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition">
                    Tutup
                </button>
                <button type="button" id="detail-edit-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Ubah Item
                </button>
            </div>
        </div>
    </div>
</div>
