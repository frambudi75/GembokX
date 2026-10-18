<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Left Navigation Sidebar (Desktop lg+) -->
    <aside class="hidden lg:block lg:col-span-1 space-y-6">
        <!-- New Item CTA -->
        <button type="button" onclick="openAddItemModal('login')" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Item Baru</span>
        </button>

        <!-- Kategori / Filter Utama -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-3 shadow-xs">
            <div class="space-y-1">
                <button type="button" onclick="VaultApp.setFilter('all')" id="filter-btn-all" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Semua Item</span>
                    </div>
                </button>

                <button type="button" onclick="VaultApp.setFilter('favorite')" id="filter-btn-favorite" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        <span>Favorit</span>
                    </div>
                </button>

                <button type="button" onclick="VaultApp.setFilter('login')" id="filter-btn-login" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        <span>Logins</span>
                    </div>
                </button>

                <button type="button" onclick="VaultApp.setFilter('card')" id="filter-btn-card" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span>Kartu Bank</span>
                    </div>
                </button>

                <button type="button" onclick="VaultApp.setFilter('note')" id="filter-btn-note" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Catatan Rahasia</span>
                    </div>
                </button>

                <button type="button" onclick="VaultApp.setFilter('identity')" id="filter-btn-identity" class="filter-nav-btn w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                        <span>Identitas</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Folders Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Folders</span>
                <button type="button" onclick="openModal('folder-modal')" class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-md transition" title="Tambah Folder">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            <div id="folder-list" class="space-y-1">
                <p class="text-xs text-gray-400 italic">Belum ada folder</p>
            </div>
        </div>

        <!-- Security Audit Card Link -->
        <a href="<?= $url('/audit') ?>" class="block p-3.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:border-blue-500/60 dark:hover:border-blue-500/60 rounded-xl transition group shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 rounded-lg group-hover:scale-105 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">Audit Keamanan Sandi</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Deteksi sandi lemah & berulang</p>
                </div>
            </div>
        </a>
    </aside>

    <!-- Main Items Vault Panel -->
    <main class="lg:col-span-3 space-y-4">
        <!-- Mobile Horizontal Category Chips (lg:hidden) -->
        <div class="lg:hidden flex items-center space-x-2 overflow-x-auto pb-2 no-scrollbar scroll-smooth">
            <button type="button" onclick="VaultApp.setFilter('all')" id="mobile-filter-chip-all" class="mobile-filter-chip px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white whitespace-nowrap shadow-xs">Semua</button>
            <button type="button" onclick="VaultApp.setFilter('favorite')" id="mobile-filter-chip-favorite" class="mobile-filter-chip px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 whitespace-nowrap">Favorit</button>
            <button type="button" onclick="VaultApp.setFilter('login')" id="mobile-filter-chip-login" class="mobile-filter-chip px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 whitespace-nowrap">Logins</button>
            <button type="button" onclick="VaultApp.setFilter('card')" id="mobile-filter-chip-card" class="mobile-filter-chip px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 whitespace-nowrap">Kartu</button>
            <button type="button" onclick="VaultApp.setFilter('note')" id="mobile-filter-chip-note" class="mobile-filter-chip px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 whitespace-nowrap">Catatan</button>
            <button type="button" onclick="VaultApp.setFilter('identity')" id="mobile-filter-chip-identity" class="mobile-filter-chip px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 whitespace-nowrap">Identitas</button>
            <button type="button" onclick="openModal('folder-sheet-modal')" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400 whitespace-nowrap flex items-center space-x-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <span>Folder</span>
            </button>
        </div>

        <!-- Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs">
            <div class="flex items-center space-x-3">
                <h2 id="vault-header-title" class="text-lg font-bold text-gray-900 dark:text-white">Semua Item Brankas</h2>
                <span id="vault-count-badge" class="px-2 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full">0</span>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button" onclick="openAddItemModal('login')" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Item Baru</span>
                </button>
            </div>
        </div>

        <!-- Vault Items List -->
        <div id="vault-items-container" class="space-y-2.5">
            <!-- Loading Skeleton -->
            <div class="p-6 text-center text-sm text-gray-400 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
                <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Menyiapkan brankas terenkripsi...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div id="vault-empty-state" class="hidden text-center py-16 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Brankas Anda Masih Kosong</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-5">Mulai simpan kata sandi akun, catatan rahasia, atau kartu perbankan Anda dengan keamanan enkripsi end-to-end.</p>
            <button type="button" onclick="openAddItemModal('login')" class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Item Pertama</span>
            </button>
        </div>
    </main>
</div>
