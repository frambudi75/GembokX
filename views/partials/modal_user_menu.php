<!-- Modal Bottom Sheet Menu Pengguna & Profil Mobile (PWA) -->
<div id="user-menu-sheet" class="modal-backdrop fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white dark:bg-gray-900 border-t sm:border border-gray-200 dark:border-gray-800 rounded-t-2xl sm:rounded-2xl max-w-sm w-full p-5 shadow-2xl relative max-h-[90vh] flex flex-col">
        <!-- Sheet Handle (Mobile Indicator) -->
        <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full mx-auto mb-3 sm:hidden"></div>

        <!-- Header Profil Pengguna -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-11 h-11 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-2xs flex-shrink-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= $url($e($user['avatar'])) ?>" class="w-full h-full object-cover" alt="Foto Profil">
                    <?php else: ?>
                        <div class="w-full h-full bg-blue-600 text-white font-bold flex items-center justify-center text-base uppercase">
                            <?= substr($e($user['username'] ?? 'U'), 0, 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="truncate">
                    <div class="flex items-center space-x-1.5">
                        <span class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            <?= $e($user['full_name'] ?? $user['username'] ?? 'Pengguna') ?>
                        </span>
                        <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                            <span class="px-1.5 py-0.2 text-[9px] font-bold uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50 rounded">Admin</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate">@<?= $e($user['username'] ?? '') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeModal('user-menu-sheet')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigasi Menu List -->
        <div class="flex-1 overflow-y-auto py-3 space-y-1">
            <!-- 1. Khusus Admin: Kelola Pengguna & Rekan -->
            <?php if (($user['role'] ?? 'user') === 'admin'): ?>
            <a href="<?= $url('/admin/users') ?>" class="w-full px-3.5 py-3 text-sm font-semibold text-blue-700 dark:text-blue-300 bg-blue-50/80 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 rounded-xl border border-blue-200/80 dark:border-blue-900/40 flex items-center justify-between transition min-h-[48px]">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold leading-tight">Kelola Pengguna</div>
                        <span class="text-[11px] font-normal text-blue-600 dark:text-blue-400">Atur akun anggota & teman</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <?php endif; ?>

            <!-- 2. Pengaturan Akun & Keamanan -->
            <a href="<?= $url('/settings') ?>" class="w-full px-3.5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-between transition min-h-[44px]">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </span>
                    <span>Pengaturan Akun & 2FA</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

            <!-- 3. Audit Keamanan Sandi -->
            <a href="<?= $url('/audit') ?>" class="w-full px-3.5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-between transition min-h-[44px]">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-blue-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </span>
                    <span>Audit Keamanan Sandi</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

            <!-- 4. Emergency Recovery Kit -->
            <a href="<?= $url('/emergency-kit') ?>" class="w-full px-3.5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-between transition min-h-[44px]">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </span>
                    <span>Emergency Kit Cadangan</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

            <!-- Quick Toggles -->
            <div class="pt-2 pb-1 border-t border-gray-100 dark:border-gray-800/80 my-2">
                <div class="grid grid-cols-2 gap-2">
                    <!-- Toggle Tema -->
                    <button type="button" onclick="ThemeManager.toggle()" class="px-3 py-2 text-xs font-semibold rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 flex items-center justify-center space-x-2 transition min-h-[44px]">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Ganti Tema</span>
                    </button>

                    <!-- Toggle Privacy Shield -->
                    <button type="button" onclick="VaultApp.togglePrivacyMode()" class="px-3 py-2 text-xs font-semibold rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 flex items-center justify-center space-x-2 transition min-h-[44px]">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>Anti-Intip</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Bawah: Kunci Brankas & Logout -->
        <div class="pt-3 border-t border-gray-200 dark:border-gray-800 space-y-2">
            <button type="button" onclick="closeModal('user-menu-sheet'); VaultApp.lockVault();" class="w-full py-2.5 px-3 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-2 border border-amber-200/80 dark:border-amber-900/50 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>Kunci Brankas Sekarang</span>
            </button>

            <a href="<?= $url('/logout') ?>" class="w-full py-2.5 px-3 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-xs font-semibold rounded-xl transition flex items-center justify-center space-x-2 border border-rose-200/80 dark:border-rose-900/50 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluar dari Akun</span>
            </a>
        </div>
    </div>
</div>
