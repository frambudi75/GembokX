<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'GembokX — Password Manager') ?></title>
    <meta name="csrf-token" content="<?= $csrf_token() ?>">
    <!-- Favicon & Touch Icon -->
    <link rel="icon" type="image/png" sizes="64x64" href="<?= $url('/assets/img/favicon.png?v=2.0') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= $url('/assets/img/favicon.svg?v=2.0') ?>">
    <link rel="apple-touch-icon" href="<?= $url('/assets/img/icon-192.png?v=2.0') ?>">
    <!-- PWA Web App Manifest & Mobile Meta -->
    <link rel="manifest" href="<?= $url('/manifest.json') ?>">
    <meta name="theme-color" content="#0b0f19">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GembokX">
    <!-- Tailwind CSS CDN -->
    <script>
        // Saring peringatan produksi CDN agar konsol browser tetap bersih
        (function() {
            const _warn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
                _warn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            750: '#262f40',
                            850: '#151d2e',
                            950: '#0b0f19'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Menlo', 'Courier New', 'monospace']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        /* Kustom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.4); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.6); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        window.PV_CONFIG = {
            baseUrl: '<?= $url('/') ?>',
            csrfToken: '<?= $csrf_token() ?>',
            autolockMinutes: <?= (int) ($autolockMinutes ?? 15) ?>,
            routes: {
                sync: '<?= $url('/api/vault/sync') ?>',
                saveItem: '<?= $url('/api/vault/save') ?>',
                deleteItem: '<?= $url('/api/vault/delete') ?>',
                toggleFavorite: '<?= $url('/api/vault/favorite') ?>',
                saveFolder: '<?= $url('/api/folder/save') ?>',
                deleteFolder: '<?= $url('/api/folder/delete') ?>',
                setup2fa: '<?= $url('/api/settings/2fa/setup') ?>',
                confirm2fa: '<?= $url('/api/settings/2fa/confirm') ?>',
                disable2fa: '<?= $url('/api/settings/2fa/disable') ?>',
                changeMasterKey: '<?= $url('/api/settings/master-key') ?>',
                updateKdf: '<?= $url('/api/settings/kdf') ?>',
                updateDiscord: '<?= $url('/api/settings/discord') ?>',
                testDiscord: '<?= $url('/api/settings/discord/test') ?>',
                updateAutoLock: '<?= $url('/api/settings/autolock') ?>',
                importVault: '<?= $url('/api/vault/import') ?>',
                updateProfile: '<?= $url('/api/profile/update') ?>',
                uploadAvatar: '<?= $url('/api/profile/avatar') ?>',
                adminUserList: '<?= $url('/api/admin/users/list') ?>',
                adminUserCreate: '<?= $url('/api/admin/users/create') ?>',
                adminUserToggleStatus: '<?= $url('/api/admin/users/toggle-status') ?>',
                adminUserChangeRole: '<?= $url('/api/admin/users/change-role') ?>',
                adminUserDelete: '<?= $url('/api/admin/users/delete') ?>'
            },
            vaultKeyMeta: <?= json_encode($vaultKey ?? []) ?>
        };
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col antialiased selection:bg-blue-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xs border-b border-gray-200 dark:border-gray-800 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <!-- Brand Logo -->
            <a href="<?= $url('/vault') ?>" class="flex items-center space-x-2.5 sm:space-x-3 group min-w-0">
                <img src="<?= $url('/assets/img/icon-192.png?v=2.0') ?>" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg shadow-xs group-hover:scale-105 transition flex-shrink-0 object-contain" alt="GembokX">
                <div class="truncate">
                    <span class="text-base font-bold tracking-tight text-gray-900 dark:text-white flex items-center">
                        GembokX <span class="hidden sm:inline-block ml-2 px-1.5 py-0.5 text-[9px] uppercase font-bold tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50 rounded">Zero-Knowledge</span>
                    </span>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium leading-none hidden sm:block">Self-Hosted Password Vault</p>
                </div>
            </a>

            <!-- Search Bar (Desktop sm+) -->
            <div class="hidden sm:flex flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="vault-search-input" placeholder="Cari item di brankas..." oninput="VaultApp.renderVaultUI(this.value)" class="w-full pl-10 pr-16 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition placeholder-gray-400">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-gray-200 dark:bg-gray-700/80 rounded border border-gray-300 dark:border-gray-600/70 shadow-2xs">Ctrl K</kbd>
                    </div>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center space-x-1 sm:space-x-2">
                <!-- Mobile Search Toggle -->
                <button type="button" onclick="const b = document.getElementById('mobile-search-bar'); b.classList.toggle('hidden'); if(!b.classList.contains('hidden')) document.getElementById('mobile-vault-search-input')?.focus();" class="p-2 sm:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Cari Item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                <!-- Admin User Manager Link (Desktop) -->
                <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                <a href="<?= $url('/admin/users') ?>" class="hidden md:inline-flex items-center space-x-1.5 px-2.5 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-900/50 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition" title="Kelola Anggota / Teman">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Pengguna</span>
                </a>
                <?php endif; ?>

                <!-- Generator Button (Desktop) -->
                <button type="button" onclick="openModal('generator-modal')" class="hidden md:flex items-center space-x-1.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    <span>Generator</span>
                </button>

                <!-- Privacy Mode / Shoulder-Surfing Shield Toggle -->
                <button type="button" id="btn-privacy-toggle" onclick="VaultApp.togglePrivacyMode()" class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Mode Anti-Intip / Privacy Shield">
                    <svg id="icon-privacy-off" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg id="icon-privacy-on" class="w-5 h-5 text-blue-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                </button>

                <!-- Theme Toggle -->
                <button type="button" onclick="ThemeManager.toggle()" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Ganti Mode Gelap/Terang">
                    <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg class="w-5 h-5 block dark:hidden text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>

                <!-- Kunci Brankas -->
                <button type="button" onclick="VaultApp.lockVault()" class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Kunci Brankas Sekarang">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </button>

                <!-- User Dropdown / Menu (Avatar) -->
                <div class="relative group">
                    <a href="<?= $url('/settings') ?>" class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Pengaturan & Profil (<?= $e($user['username'] ?? '') ?>)">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= $url($e($user['avatar'])) ?>" class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shadow-2xs" alt="Avatar">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs uppercase shadow-xs">
                                <?= substr($e($user['username'] ?? 'U'), 0, 1) ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Logout (Desktop) -->
                <a href="<?= $url('/logout') ?>" class="hidden sm:inline-flex p-2 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>

        <!-- Mobile Search Dropdown Bar -->
        <div id="mobile-search-bar" class="hidden sm:hidden border-t border-gray-200 dark:border-gray-800 bg-gray-50/95 dark:bg-gray-900/95 px-4 py-2.5 transition">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="mobile-vault-search-input" placeholder="Cari item di brankas..." oninput="VaultApp.renderVaultUI(this.value)" class="w-full pl-9 pr-8 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 text-gray-900 dark:text-white">
                <button type="button" onclick="document.getElementById('mobile-vault-search-input').value=''; VaultApp.renderVaultUI('');" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Grid with Safe Area Padding -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 lg:pb-8">
        <?= $content ?>
    </div>

    <!-- Mobile Bottom Navigation Bar (Fixed for phone/tablet, hidden on desktop lg) -->
    <nav class="fixed bottom-0 inset-x-0 z-30 lg:hidden bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 pb-[env(safe-area-inset-bottom,0px)] shadow-lg transition-colors">
        <div class="grid grid-cols-5 h-16 max-w-md mx-auto items-center text-center">
            <!-- 1. Vault -->
            <button type="button" onclick="VaultApp.setFilter('all'); window.scrollTo({top: 0, behavior: 'smooth'});" class="flex flex-col items-center justify-center py-1 text-blue-600 dark:text-blue-400 hover:opacity-80 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span class="text-[10px] font-medium mt-1">Brankas</span>
            </button>

            <!-- 2. Folders -->
            <button type="button" onclick="openModal('folder-sheet-modal')" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <span class="text-[10px] font-medium mt-1">Folder</span>
            </button>

            <!-- 3. Primary FAB: Tambah Item -->
            <button type="button" onclick="openAddItemModal('login')" class="flex flex-col items-center justify-center -mt-5 group" title="Tambah Item Baru">
                <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/30 group-active:scale-95 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400 mt-0.5">Tambah</span>
            </button>

            <!-- 4. Generator -->
            <button type="button" onclick="openModal('generator-modal')" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <span class="text-[10px] font-medium mt-1">Generator</span>
            </button>

            <!-- 5. Settings / Menu -->
            <a href="<?= $url('/settings') ?>" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[10px] font-medium mt-1">Menu</span>
            </a>
        </div>
    </nav>

    <!-- Core Scripts (Load first so partials and components can use them) -->
    <script src="<?= $url('/assets/js/argon2.umd.min.js') ?>"></script>
    <script src="<?= $url('/assets/js/crypto.js') ?>"></script>
    <script src="<?= $url('/assets/js/generator.js') ?>"></script>
    <script src="<?= $url('/assets/js/biometrics.js') ?>"></script>
    <script src="<?= $url('/assets/js/app.js?v=1.4') ?>"></script>
    <script src="<?= $url('/assets/js/vault.js?v=1.4') ?>"></script>

    <!-- Modals Terintegrasi -->
    <?php include dirname(__DIR__) . '/partials/modal_unlock.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_item.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_detail.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_share_secret.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_generator.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_folder.php'; ?>
    <?php include dirname(__DIR__) . '/partials/modal_folder_sheet.php'; ?>

    <!-- Service Worker Registration & PWA Install Listener -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= $url('/sw.js') ?>')
                    .then(reg => console.log('[PWA] Service Worker terdaftar (Scope:', reg.scope, ')'))
                    .catch(err => console.warn('[PWA] Service Worker gagal daftar:', err));
            });
        }

        // PWA Install Prompt Listener
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            window.PWA_PROMPT = deferredPrompt;
            console.log('[PWA] Aplikasi siap diinstal ke home screen');
        });
    </script>
</body>
</html>
