<!-- Modal Sheet Filter Folder untuk Mobile / Tablet -->
<div id="folder-sheet-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white dark:bg-gray-900 border-t sm:border border-gray-200 dark:border-gray-800 rounded-t-2xl sm:rounded-2xl max-w-sm w-full p-5 shadow-2xl relative max-h-[85vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Pilih Folder</h3>
            </div>
            <button type="button" onclick="closeModal('folder-sheet-modal')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Folder List -->
        <div class="flex-1 overflow-y-auto py-3 space-y-1" id="mobile-folder-sheet-list">
            <!-- Diisi secara dinamis melalui JavaScript -->
            <button type="button" onclick="VaultApp.setFilter('all'); closeModal('folder-sheet-modal')" class="w-full text-left px-3.5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-between">
                <span>📁 Semua Folder</span>
            </button>
        </div>

        <!-- Action Tambah Folder Baru -->
        <div class="pt-3 border-t border-gray-200 dark:border-gray-800">
            <button type="button" onclick="closeModal('folder-sheet-modal'); openModal('folder-modal')" class="w-full py-2.5 px-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Buat Folder Baru</span>
            </button>
        </div>
    </div>
</div>
