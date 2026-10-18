/**
 * PassVault — Vault State Manager (Browser In-Memory Decryption)
 */

const VaultApp = (() => {
    'use strict';

    let activeVaultKey = null;
    let decryptedItems = [];
    let decryptedFolders = [];
    let currentFilterType = 'all';
    let currentFolderId = null;
    let autoLockTimer = null;
    let activeDetailItem = null;
    let currentDensity = localStorage.getItem('pv_density') || 'card';
    let isPrivacyMode = localStorage.getItem('pv_privacy_mode') === 'true';
    const IDLE_TIMEOUT_MS = 15 * 60 * 1000; // 15 Menit

    const getDomainFromUrl = (urlStr) => {
        if (!urlStr) return '';
        try {
            let clean = urlStr.trim();
            if (!clean.startsWith('http://') && !clean.startsWith('https://')) {
                clean = 'https://' + clean;
            }
            const u = new URL(clean);
            const host = u.hostname.replace(/^www\./, '').toLowerCase();
            // Validasi format domain publik: harus memiliki minimal 1 titik dan TLD valid minimal 2 karakter
            // Mencegah request 404 ke kata seperti 'tes', 'ssh', 'localhost'
            if (/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,}$/.test(host)) {
                return host;
            }
            return '';
        } catch (e) {
            return '';
        }
    };

    const getMonogramData = (name) => {
        const clean = (name || 'V').trim();
        const words = clean.split(/\s+/).filter(Boolean);
        let letters = 'V';
        if (words.length > 1 && words[0] && words[1]) {
            letters = words[0].charAt(0) + words[1].charAt(0);
        } else if (clean.length > 0) {
            letters = clean.slice(0, 2);
        }
        letters = letters.toUpperCase();

        const colors = [
            'bg-blue-600 text-white',
            'bg-emerald-600 text-white',
            'bg-purple-600 text-white',
            'bg-amber-600 text-white',
            'bg-rose-600 text-white',
            'bg-indigo-600 text-white',
            'bg-teal-600 text-white'
        ];
        let hash = 0;
        for (let i = 0; i < clean.length; i++) hash = clean.charCodeAt(i) + ((hash << 5) - hash);
        const colorClass = colors[Math.abs(hash) % colors.length];
        return { letters, colorClass, singleLetter: letters.charAt(0) || 'V' };
    };

    /**
     * Inisialisasi Durasi Auto-Lock Timer
     */
    const getAutoLockTimeoutMs = () => {
        const stored = localStorage.getItem('pv_autolock_minutes');
        let minutes = stored !== null ? parseInt(stored, 10) : (window.PV_CONFIG && window.PV_CONFIG.autolockMinutes !== undefined ? window.PV_CONFIG.autolockMinutes : 15);
        if (isNaN(minutes)) minutes = 15;
        if (minutes <= 0) return 0; // 0 = Dinonaktifkan (Jangan Pernah)
        return minutes * 60 * 1000;
    };

    const resetAutoLock = () => {
        if (autoLockTimer) clearTimeout(autoLockTimer);
        const timeoutMs = getAutoLockTimeoutMs();
        if (timeoutMs <= 0) return;
        autoLockTimer = setTimeout(() => {
            lockVault();
        }, timeoutMs);
    };

    const setupActivityListeners = () => {
        ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll'].forEach(evt => {
            window.addEventListener(evt, resetAutoLock, { passive: true });
        });
    };

    /**
     * Kunci Brankas (Hapus Kunci dari RAM dan Sesi Tab)
     */
    const lockVault = () => {
        activeVaultKey = null;
        decryptedItems = [];
        try { sessionStorage.removeItem('pv_vault_key'); } catch (e) {}
        if (typeof showLockModal === 'function') {
            showLockModal();
        }
    };

    /**
     * Simpan Kunci Sesi di Tab Browser (sessionStorage)
     */
    const saveSessionKey = async (vaultKey) => {
        try {
            const raw = await window.crypto.subtle.exportKey('raw', vaultKey);
            const b64 = VaultCrypto.bufferToBase64(raw);
            sessionStorage.setItem('pv_vault_key', b64);
        } catch (e) {
            console.warn('[Vault] Gagal menyimpan kunci sesi tab:', e);
        }
    };

    /**
     * Pulihkan Kunci Sesi dari Tab Browser jika Masih Aktif
     */
    const tryRestoreSession = async () => {
        try {
            const b64 = sessionStorage.getItem('pv_vault_key');
            if (!b64) return false;
            const buf = VaultCrypto.base64ToBuffer(b64);
            const importedKey = await window.crypto.subtle.importKey(
                'raw',
                buf,
                { name: 'AES-GCM', length: 256 },
                true,
                ['encrypt', 'decrypt']
            );
            activeVaultKey = importedKey;
            resetAutoLock();
            return true;
        } catch (e) {
            try { sessionStorage.removeItem('pv_vault_key'); } catch (_) {}
            return false;
        }
    };

    /**
     * Buka Kunci Brankas dengan Master Password (Mendukung PBKDF2 & Argon2id)
     */
    const unlockWithMasterPassword = async (masterPassword, encryptedKeyB64, ivB64, saltHex, iterations, algorithm = 'pbkdf2', memory = 65536, parallelism = 4) => {
        try {
            const masterKey = await VaultCrypto.deriveMasterKey(masterPassword, saltHex, iterations, algorithm, memory, parallelism);
            const vaultKey = await VaultCrypto.decryptVaultKey(encryptedKeyB64, ivB64, masterKey);
            activeVaultKey = vaultKey;
            await saveSessionKey(vaultKey);
            resetAutoLock();
            return true;
        } catch (err) {
            console.error('Gagal membuka kunci:', err);
            return false;
        }
    };

    /**
     * Muat dan Dekripsi Data dari Server
     */
    const syncAndDecrypt = async () => {
        if (!activeVaultKey) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.sync, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                }
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            // Dekripsi Folder
            decryptedFolders = [];
            for (const f of data.folders) {
                const name = await VaultCrypto.decryptData(f.encrypted_name, f.iv, activeVaultKey);
                decryptedFolders.push({
                    id: f.id,
                    name: (typeof name === 'object' && name !== null ? name.name : name) || 'Folder Tanpa Nama'
                });
            }

            // Dekripsi Items
            decryptedItems = [];
            for (const item of data.items) {
                const payload = await VaultCrypto.decryptData(item.encrypted_payload, item.iv, activeVaultKey);
                if (payload) {
                    decryptedItems.push({
                        id: item.id,
                        folder_id: item.folder_id,
                        item_type: item.item_type,
                        is_favorite: parseInt(item.is_favorite) === 1,
                        updated_at: item.updated_at,
                        data: payload
                    });
                }
            }

            renderVaultUI();
            renderFolderNav();
        } catch (err) {
            console.error('Gagal sinkronisasi brankas:', err);
            AppToast.show('Gagal sinkronisasi data brankas: ' + err.message, 'error');
        }
    };

    /**
     * Render Daftar Item di Dashboard
     */
    const renderVaultUI = (searchQuery = '') => {
        const container = document.getElementById('vault-items-container');
        const emptyState = document.getElementById('vault-empty-state');
        const countBadge = document.getElementById('vault-count-badge');
        if (!container) return;

        let filtered = decryptedItems;

        // Filter berdasarkan Tipe
        if (currentFilterType === 'favorite') {
            filtered = filtered.filter(i => i.is_favorite);
        } else if (currentFilterType !== 'all') {
            filtered = filtered.filter(i => i.item_type === currentFilterType);
        }

        // Filter berdasarkan Folder
        if (currentFolderId !== null) {
            filtered = filtered.filter(i => i.folder_id == currentFolderId);
        }

        // Filter Pencarian Teks (0ms Realtime)
        if (searchQuery.trim() !== '') {
            const q = searchQuery.toLowerCase();
            filtered = filtered.filter(i => {
                const name = (i.data.name || '').toLowerCase();
                const username = (i.data.username || '').toLowerCase();
                const url = (i.data.url || '').toLowerCase();
                const notes = (i.data.notes || '').toLowerCase();
                return name.includes(q) || username.includes(q) || url.includes(q) || notes.includes(q);
            });
        }

        if (countBadge) {
            countBadge.textContent = filtered.length;
        }

        if (filtered.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        updateDensityUI();
        initPrivacyMode();

        container.className = currentDensity === 'compact' ? 'space-y-1.5' : 'space-y-2.5';

        container.innerHTML = filtered.map(item => {
            const d = item.data;
            const isFav = item.is_favorite;
            const domain = (d.url ? getDomainFromUrl(d.url) : '') || getDomainFromUrl(d.name || '');

            let subtext = '';
            if (item.item_type === 'login') {
                subtext = d.username || d.url || 'Tidak ada username';
            } else if (item.item_type === 'card') {
                subtext = d.number ? '•••• ' + d.number.slice(-4) : (d.brand || 'Kartu');
            } else if (item.item_type === 'note') {
                subtext = 'Catatan Terenkripsi';
            } else if (item.item_type === 'identity') {
                subtext = d.full_name || 'Identitas Terenkripsi';
            }

            const folder = decryptedFolders.find(f => f.id == item.folder_id);
            const folderTag = folder ? `<span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600/50 rounded ml-2 flex-shrink-0">${escapeHtml(folder.name)}</span>` : '';

            // Icon resolution: Favicon -> Monogram -> Type SVG
            let iconHtml = '';
            let iconSmall = '';

            if (item.item_type === 'login') {
                const mono = getMonogramData(d.name);
                if (domain) {
                    iconHtml = `
                    <div class="w-10 h-10 rounded-xl relative overflow-hidden flex-shrink-0 shadow-2xs border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-center">
                        <div class="w-full h-full ${mono.colorClass} font-bold text-xs flex items-center justify-center uppercase tracking-wider select-none">
                            ${escapeHtml(mono.letters)}
                        </div>
                        <img src="https://www.google.com/s2/favicons?domain=${encodeURIComponent(domain)}&sz=64" 
                             class="absolute inset-0 w-full h-full p-2 object-contain bg-white dark:bg-gray-800" 
                             alt="" 
                             loading="lazy" 
                             onerror="this.remove();" />
                    </div>`;
                    iconSmall = `
                    <div class="w-5 h-5 rounded relative overflow-hidden flex-shrink-0 flex items-center justify-center">
                        <div class="w-full h-full bg-blue-600/20 text-blue-500 font-bold text-[9px] flex items-center justify-center uppercase">
                            ${escapeHtml(mono.singleLetter)}
                        </div>
                        <img src="https://www.google.com/s2/favicons?domain=${encodeURIComponent(domain)}&sz=32" 
                             class="absolute inset-0 w-full h-full object-contain bg-white dark:bg-gray-800" 
                             alt="" 
                             loading="lazy" 
                             onerror="this.remove();" />
                    </div>`;
                } else {
                    iconHtml = `
                    <div class="w-10 h-10 rounded-xl ${mono.colorClass} font-bold text-xs flex items-center justify-center uppercase tracking-wider shadow-2xs border border-gray-200/80 dark:border-gray-700/80 flex-shrink-0 select-none">
                        ${escapeHtml(mono.letters)}
                    </div>`;
                    iconSmall = `
                    <div class="w-5 h-5 rounded bg-blue-600/20 text-blue-500 font-bold text-[9px] flex items-center justify-center uppercase flex-shrink-0">
                        ${escapeHtml(mono.singleLetter)}
                    </div>`;
                }
            } else if (item.item_type === 'card') {
                iconHtml = `<div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 p-2 flex items-center justify-center flex-shrink-0 shadow-2xs border border-emerald-100 dark:border-emerald-900/40"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></div>`;
                iconSmall = `<div class="w-4 h-4 text-emerald-500 flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></div>`;
            } else if (item.item_type === 'note') {
                iconHtml = `<div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 p-2 flex items-center justify-center flex-shrink-0 shadow-2xs border border-amber-100 dark:border-amber-900/40"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>`;
                iconSmall = `<div class="w-4 h-4 text-amber-500 flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>`;
            } else {
                iconHtml = `<div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 p-2 flex items-center justify-center flex-shrink-0 shadow-2xs border border-purple-100 dark:border-purple-900/40"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg></div>`;
                iconSmall = `<div class="w-4 h-4 text-purple-500 flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg></div>`;
            }

            // COMPACT ROW DENSITY
            if (currentDensity === 'compact') {
                return `
                <div class="group flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl transition duration-150 shadow-2xs cursor-pointer text-xs" onclick="VaultApp.showDetailModal(${item.id})">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="flex-shrink-0">
                            ${iconSmall}
                        </div>
                        <div class="min-w-0 flex items-center space-x-2">
                            <span class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">${escapeHtml(d.name || 'Tanpa Judul')}</span>
                            ${folderTag}
                            ${(d.attachments && d.attachments.length > 0) ? `<span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 rounded" title="${d.attachments.length} berkas terlampir">📎 ${d.attachments.length}</span>` : ''}
                            <span class="privacy-sensitive text-gray-400 truncate hidden sm:inline">• ${escapeHtml(subtext)}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-1 flex-shrink-0" onclick="event.stopPropagation()">
                        ${d.username ? `
                        <button type="button" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Salin Username" onclick="VaultApp.copyToClipboard('${escapeJs(d.username)}', 'Username disalin!')">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </button>` : ''}

                        ${d.password ? `
                        <button type="button" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Salin Kata Sandi" onclick="VaultApp.copyToClipboard('${escapeJs(d.password)}', 'Kata sandi disalin!')">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        </button>` : ''}

                        <button type="button" class="w-7 h-7 flex items-center justify-center ${isFav ? 'text-amber-500' : 'text-gray-300 dark:text-gray-600 hover:text-amber-500'} rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="${isFav ? 'Hapus dari Favorit' : 'Jadikan Favorit'}" onclick="VaultApp.toggleFavorite(${item.id})">
                            <svg class="w-3.5 h-3.5" fill="${isFav ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </button>
                    </div>
                </div>`;
            }

            // STANDARD CARD DENSITY
            return `
            <div class="group flex items-center justify-between p-3.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl transition duration-150 shadow-xs cursor-pointer" onclick="VaultApp.showDetailModal(${item.id})">
                <div class="flex items-center space-x-3.5 min-w-0">
                    ${iconHtml}
                    <div class="min-w-0">
                        <div class="flex items-center">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">${escapeHtml(d.name || 'Tanpa Judul')}</h4>
                            ${folderTag}
                            ${(d.attachments && d.attachments.length > 0) ? `<span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 rounded ml-1.5" title="${d.attachments.length} berkas terlampir">📎 ${d.attachments.length}</span>` : ''}
                        </div>
                        <p class="privacy-sensitive text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">${escapeHtml(subtext)}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-1.5 flex-shrink-0" onclick="event.stopPropagation()">
                    ${d.username ? `
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Salin Username" onclick="VaultApp.copyToClipboard('${escapeJs(d.username)}', 'Username disalin!')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </button>` : ''}

                    ${d.password ? `
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Salin Kata Sandi" onclick="VaultApp.copyToClipboard('${escapeJs(d.password)}', 'Kata sandi disalin!')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </button>` : ''}

                    <button type="button" class="w-10 h-10 flex items-center justify-center ${isFav ? 'text-amber-500' : 'text-gray-300 dark:text-gray-600 hover:text-amber-500'} rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="${isFav ? 'Hapus dari Favorit' : 'Jadikan Favorit'}" onclick="VaultApp.toggleFavorite(${item.id})">
                        <svg class="w-4 h-4" fill="${isFav ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    </button>
                </div>
            </div>`;
        }).join('');
    };

    /**
     * Tampilkan Modal Detail Item
     */
    const showDetailModal = (itemId) => {
        const item = decryptedItems.find(i => i.id == itemId);
        if (!item) return;

        activeDetailItem = item;
        const d = item.data;

        const nameEl = document.getElementById('detail-name');
        const badgeEl = document.getElementById('detail-type-badge');
        if (nameEl) nameEl.textContent = d.name || 'Tanpa Judul';
        if (badgeEl) badgeEl.textContent = item.item_type;

        // Folder badge
        const folder = decryptedFolders.find(f => f.id == item.folder_id);
        const fBadge = document.getElementById('detail-folder-badge');
        if (fBadge) {
            if (folder) {
                fBadge.textContent = folder.name;
                fBadge.classList.remove('hidden');
            } else {
                fBadge.classList.add('hidden');
            }
        }

        // Fav star
        const favBtn = document.getElementById('detail-fav-btn');
        if (favBtn) {
            favBtn.innerHTML = `<svg class="w-5 h-5 ${item.is_favorite ? 'text-amber-500 fill-current' : 'text-gray-400'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>`;
        }

        // Render Fields
        const body = document.getElementById('detail-fields-body');
        if (!body) return;
        let html = '';

        if (item.item_type === 'login') {
            if (d.username) {
                html += renderDetailField('Username / Email', d.username, true);
            }
            if (d.password) {
                html += renderPasswordField('Kata Sandi', d.password);
            }
            if (d.url) {
                html += `
                <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Situs Web</span>
                    <div class="flex items-center justify-between">
                        <a href="${escapeHtml(d.url)}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[80%]">${escapeHtml(d.url)}</a>
                        <button type="button" onclick="VaultApp.copyToClipboard('${escapeJs(d.url)}', 'URL disalin!')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Salin URL">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        </button>
                    </div>
                </div>`;
            }
            if (d.totp_seed) {
                html += renderDetailField('TOTP Seed Kunci', d.totp_seed, true, true);
            }
        } else if (item.item_type === 'card') {
            if (d.cardholder) html += renderDetailField('Nama Pemegang', d.cardholder, true);
            if (d.number) html += renderDetailField('Nomor Kartu', d.number, true, true);
            if (d.exp_month || d.exp_year) html += renderDetailField('Kadaluarsa', `${d.exp_month || '--'} / ${d.exp_year || '----'}`);
            if (d.cvv) html += renderPasswordField('Kode Keamanan (CVV)', d.cvv);
        } else if (item.item_type === 'note') {
            html += `
            <div class="p-3.5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-2">Isi Catatan Rahasia</span>
                <div class="text-sm font-mono whitespace-pre-wrap select-all text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">${escapeHtml(d.notes || 'Catatan kosong')}</div>
                <div class="mt-2 flex justify-end">
                    <button type="button" onclick="VaultApp.copyToClipboard('${escapeJs(d.notes || '')}', 'Catatan disalin!')" class="px-3 py-1.5 text-xs text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition font-medium">Salin Seluruh Catatan</button>
                </div>
            </div>`;
        } else if (item.item_type === 'identity') {
            if (d.full_name) html += renderDetailField('Nama Lengkap', d.full_name, true);
            if (d.id_number) html += renderDetailField('No. KTP / Identitas', d.id_number, true, true);
            if (d.address) html += renderDetailField('Alamat', d.address, true);
        }

        if (item.item_type !== 'note' && d.notes) {
            html += `
            <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Catatan Tambahan</span>
                <p class="privacy-sensitive text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${escapeHtml(d.notes)}</p>
            </div>`;
        }

        // Render Lampiran Berkas Terenkripsi (Zero-Knowledge)
        if (d.attachments && Array.isArray(d.attachments) && d.attachments.length > 0) {
            html += `
            <div class="p-3.5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-2.5 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <span>Lampiran Berkas Terenkripsi (${d.attachments.length})</span>
                </span>
                <div class="space-y-2">
                    ${d.attachments.map((att, idx) => `
                        <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs">
                            <div class="flex items-center space-x-2.5 truncate pr-2">
                                <div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="truncate">
                                    <span class="font-medium text-gray-900 dark:text-gray-100 truncate block">${escapeHtml(att.name)}</span>
                                    <span class="text-[10px] text-gray-400 block">${formatFileSize(att.size)}</span>
                                </div>
                            </div>
                            <button type="button" onclick="VaultApp.downloadAttachment(${idx})" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-300 font-semibold rounded-md transition flex items-center space-x-1 flex-shrink-0" title="Unduh Berkas yang Sudah Didekripsi">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span>Unduh</span>
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>`;
        }

        body.innerHTML = html;

        // Pasang Event Tombol
        const delBtn = document.getElementById('detail-delete-btn');
        if (delBtn) delBtn.onclick = () => deleteItem(item.id);
        const editBtn = document.getElementById('detail-edit-btn');
        if (editBtn) editBtn.onclick = () => openEditItemModal(item);

        openModal('detail-modal');
    };

    const formatFileSize = (bytes) => {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    };

    const downloadAttachment = (idx) => {
        if (!activeDetailItem || !activeDetailItem.data || !activeDetailItem.data.attachments) return;
        const att = activeDetailItem.data.attachments[idx];
        if (!att || !att.data) {
            AppToast.show('Data berkas lampiran tidak ditemukan.', 'error');
            return;
        }

        try {
            const byteCharacters = atob(att.data);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], { type: att.type || 'application/octet-stream' });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = att.name || 'lampiran';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(() => URL.revokeObjectURL(link.href), 1000);
            AppToast.show(`Mengunduh ${att.name}...`, 'success');
        } catch (err) {
            AppToast.show('Gagal mengunduh berkas: ' + err.message, 'error');
        }
    };

    function renderDetailField(label, val, canCopy = false, isMono = false) {
        return `
        <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">${escapeHtml(label)}</span>
            <div class="flex items-center justify-between">
                <span class="privacy-sensitive text-sm ${isMono ? 'font-mono' : ''} text-gray-900 dark:text-gray-100 truncate">${escapeHtml(val)}</span>
                ${canCopy ? `
                <button type="button" onclick="VaultApp.copyToClipboard('${escapeJs(val)}', '${escapeJs(label)} disalin!')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Salin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                </button>` : ''}
            </div>
        </div>`;
    }

    function renderPasswordField(label, pwd) {
        const randId = 'pwd_' + Math.random().toString(36).substr(2, 9);
        return `
        <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">${escapeHtml(label)}</span>
            <div class="flex items-center justify-between">
                <span id="${randId}" data-real="${escapeHtml(pwd)}" class="privacy-sensitive text-sm font-mono text-gray-900 dark:text-gray-100 truncate">••••••••••••</span>
                <div class="flex items-center space-x-1">
                    <button type="button" onclick="VaultApp.toggleDetailPassword('${randId}')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Lihat/Sembunyikan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    <button type="button" onclick="VaultApp.copyToClipboard('${escapeJs(pwd)}', '${escapeJs(label)} disalin!')" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Salin">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </button>
                </div>
            </div>
        </div>`;
    }

    const toggleDetailPassword = (id) => {
        const el = document.getElementById(id);
        if (!el) return;
        const real = el.getAttribute('data-real');
        if (el.textContent === '••••••••••••') {
            el.textContent = real;
        } else {
            el.textContent = '••••••••••••';
        }
    };

    const handleDetailToggleFav = () => {
        if (!activeDetailItem) return;
        toggleFavorite(activeDetailItem.id);
        activeDetailItem.is_favorite = !activeDetailItem.is_favorite;
        const favBtn = document.getElementById('detail-fav-btn');
        if (favBtn) {
            favBtn.innerHTML = `<svg class="w-5 h-5 ${activeDetailItem.is_favorite ? 'text-amber-500 fill-current' : 'text-gray-400'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>`;
        }
    };

    const openEditItemModal = (item) => {
        closeModal('detail-modal');
        const titleEl = document.getElementById('item-modal-title');
        if (titleEl) titleEl.textContent = 'Ubah Item Brankas';
        document.getElementById('item-id').value = item.id;
        document.getElementById('item-is-favorite').value = item.is_favorite ? '1' : '0';
        document.getElementById('item-name').value = item.data.name || '';
        document.getElementById('item-folder-id').value = item.folder_id || '';

        if (typeof setItemType === 'function') {
            setItemType(item.item_type);
        }

        const d = item.data;
        if (item.item_type === 'login') {
            document.getElementById('login-username').value = d.username || '';
            document.getElementById('login-password').value = d.password || '';
            document.getElementById('login-url').value = d.url || '';
            document.getElementById('login-totp').value = d.totp_seed || '';
            document.getElementById('common-notes').value = d.notes || '';
            if (typeof updateItemPasswordStrength === 'function') {
                updateItemPasswordStrength(d.password || '');
            }
        } else if (item.item_type === 'card') {
            document.getElementById('card-holder').value = d.cardholder || '';
            document.getElementById('card-brand').value = d.brand || 'Visa';
            document.getElementById('card-number').value = d.number || '';
            document.getElementById('card-exp-month').value = d.exp_month || '';
            document.getElementById('card-exp-year').value = d.exp_year || '';
            document.getElementById('card-cvv').value = d.cvv || '';
            document.getElementById('common-notes').value = d.notes || '';
        } else if (item.item_type === 'note') {
            document.getElementById('note-content').value = d.notes || '';
        } else if (item.item_type === 'identity') {
            document.getElementById('identity-fullname').value = d.full_name || '';
            document.getElementById('identity-idnumber').value = d.id_number || '';
            document.getElementById('identity-address').value = d.address || '';
            document.getElementById('common-notes').value = d.notes || '';
        }

        if (typeof setItemAttachments === 'function') {
            setItemAttachments(d.attachments || []);
        }

        openModal('item-modal');
    };

    /**
     * Render Navigasi Folder
     */
    const renderFolderNav = () => {
        const list = document.getElementById('folder-list');
        const sheetList = document.getElementById('mobile-folder-sheet-list');
        const select = document.getElementById('item-folder-id');

        const html = decryptedFolders.map(f => {
            const count = decryptedItems.filter(i => i.folder_id == f.id).length;
            const isActive = currentFolderId === f.id;
            return `
            <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg ${isActive ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-750'} transition" onclick="VaultApp.setFolder(${f.id}); if(typeof closeModal==='function') closeModal('folder-sheet-modal');">
                <div class="flex items-center space-x-2 truncate">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    <span class="truncate">${escapeHtml(f.name)}</span>
                </div>
                <span class="text-xs text-gray-400">${count}</span>
            </button>`;
        }).join('');

        if (list) {
            list.innerHTML = html || '<p class="text-xs text-gray-400 italic px-2 py-1">Belum ada folder</p>';
        }

        if (sheetList) {
            sheetList.innerHTML = `
            <button type="button" onclick="VaultApp.setFolder(null); closeModal('folder-sheet-modal');" class="w-full text-left px-3.5 py-2.5 text-sm font-medium ${currentFolderId === null ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'} rounded-xl transition flex items-center justify-between">
                <span>📁 Semua Folder</span>
                <span class="text-xs text-gray-400">${decryptedItems.length}</span>
            </button>
            ${html}
            `;
        }

        if (select) {
            select.innerHTML = '<option value="">-- Tanpa Folder --</option>' + 
                decryptedFolders.map(f => `<option value="${f.id}">${escapeHtml(f.name)}</option>`).join('');
        }
    };

    let clipboardClearTimer = null;

    /**
     * Salin ke Clipboard dengan Auto-Clear Otomatis demi Keamanan
     */
    const copyToClipboard = async (text, message = 'Disalin ke clipboard!') => {
        try {
            await navigator.clipboard.writeText(text);

            const timeoutSec = parseInt(localStorage.getItem('pv_clipboard_clear_seconds') || '30', 10);
            if (timeoutSec > 0) {
                AppToast.show(`${message} (Dibersihkan dalam ${timeoutSec} detik)`, 'success');

                if (clipboardClearTimer) clearTimeout(clipboardClearTimer);
                clipboardClearTimer = setTimeout(async () => {
                    try {
                        const current = await navigator.clipboard.readText();
                        if (current === text) {
                            await navigator.clipboard.writeText('');
                            AppToast.show('Riwayat clipboard telah dibersihkan secara otomatis.', 'info');
                        }
                    } catch (_) {
                        // Jika readText ditolak browser, tetap timpa dengan teks kosong
                        try { await navigator.clipboard.writeText(''); } catch (__) {}
                    }
                }, timeoutSec * 1000);
            } else {
                AppToast.show(message, 'success');
            }
        } catch (err) {
            AppToast.show('Gagal menyalin: ' + err.message, 'error');
        }
    };

    /**
     * Simpan Item Baru / Edit
     */
    const saveItem = async (formData) => {
        if (!activeVaultKey) {
            AppToast.show('Brankas terkunci. Buka brankas terlebih dahulu.', 'error');
            return;
        }

        try {
            const encrypted = await VaultCrypto.encryptData(formData.payload, activeVaultKey);
            const body = {
                id: formData.id || null,
                folder_id: formData.folder_id || null,
                item_type: formData.item_type || 'login',
                is_favorite: formData.is_favorite ? 1 : 0,
                encrypted_payload: encrypted.ciphertext,
                iv: encrypted.iv,
                auth_tag: encrypted.authTag,
                csrf_token: window.PV_CONFIG.csrfToken
            };

            const res = await fetch(window.PV_CONFIG.routes.saveItem, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify(body)
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            await syncAndDecrypt();
            closeModal('item-modal');
        } catch (err) {
            AppToast.show('Gagal menyimpan: ' + err.message, 'error');
        }
    };

    /**
     * Hapus Item
     */
    const deleteItem = async (id) => {
        if (!confirm('Yakin ingin menghapus item ini dari brankas secara permanen?')) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.deleteItem, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({ id: id, csrf_token: window.PV_CONFIG.csrfToken })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            await syncAndDecrypt();
            closeModal('detail-modal');
        } catch (err) {
            AppToast.show('Gagal menghapus: ' + err.message, 'error');
        }
    };

    /**
     * Toggle Favorite
     */
    const toggleFavorite = async (id) => {
        try {
            const res = await fetch(window.PV_CONFIG.routes.toggleFavorite, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({ id: id, csrf_token: window.PV_CONFIG.csrfToken })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            const it = decryptedItems.find(i => i.id == id);
            if (it) it.is_favorite = !it.is_favorite;
            renderVaultUI();
        } catch (err) {
            AppToast.show('Gagal memperbarui favorit: ' + err.message, 'error');
        }
    };

    const setFilter = (type) => {
        currentFilterType = type;
        currentFolderId = null;
        document.querySelectorAll('.filter-nav-btn').forEach(b => {
            b.classList.remove('bg-blue-50', 'text-blue-700', 'dark:bg-blue-900/30', 'dark:text-blue-400');
        });
        const activeBtn = document.getElementById(`filter-btn-${type}`);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-50', 'text-blue-700', 'dark:bg-blue-900/30', 'dark:text-blue-400');
        }

        // Sinkronisasi chip filter mobile
        document.querySelectorAll('.mobile-filter-chip').forEach(c => {
            c.classList.remove('bg-blue-600', 'text-white', 'shadow-xs');
            c.classList.add('bg-white', 'dark:bg-gray-800', 'border', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
        });
        const activeChip = document.getElementById(`mobile-filter-chip-${type}`);
        if (activeChip) {
            activeChip.classList.remove('bg-white', 'dark:bg-gray-800', 'border', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
            activeChip.classList.add('bg-blue-600', 'text-white', 'shadow-xs');
        }

        renderVaultUI();
    };

    const setFolder = (id) => {
        currentFolderId = id;
        currentFilterType = 'all';
        renderVaultUI();
        renderFolderNav();
    };

    const escapeHtml = (str) => {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const escapeJs = (str) => {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
    };

    /**
     * Membuat Tautan Rahasia Sekali Pakai (Burn-After-Reading) untuk Item Aktif
     */
    const createSecretShareLink = async () => {
        if (!activeDetailItem) {
            AppToast.show('Pilih item terlebih dahulu.', 'error');
            return;
        }

        try {
            AppToast.show('Mengenkripsi data rahasia dengan kunci sekali pakai...', 'info');

            // 1. Generate AES-256 Symmetric Key sekali pakai di browser
            const transferKey = await VaultCrypto.generateVaultKey();
            const rawTransferKey = await window.crypto.subtle.exportKey('raw', transferKey);
            const keyB64 = VaultCrypto.bufferToBase64(rawTransferKey);

            // 2. Enkripsi payload item dengan transfer key
            const enc = await VaultCrypto.encryptData(activeDetailItem.data, transferKey);

            // 3. Kirim ciphertext ke server
            const res = await fetch(window.PV_CONFIG.baseUrl + 'api/secret/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    encrypted_payload: enc.ciphertext,
                    iv: enc.iv,
                    auth_tag: enc.authTag,
                    expires_hours: 24,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal membuat tautan rahasia.');

            // 4. Susun Full URL dengan fragment key (#)
            const fullUrl = window.location.origin + window.PV_CONFIG.baseUrl + 'secret/' + data.token + '#' + keyB64;

            const urlInput = document.getElementById('share-secret-url-input');
            if (urlInput) urlInput.value = fullUrl;

            openModal('modal-share-secret');
            AppToast.show('Tautan rahasia berhasil dibuat!', 'success');
        } catch (err) {
            AppToast.show('Gagal membagikan rahasia: ' + err.message, 'error');
        }
    };

    /**
     * Pengaturan Kerapatan Tampilan (Density Mode)
     */
    const setDensity = (mode) => {
        currentDensity = mode;
        try { localStorage.setItem('pv_density', mode); } catch (e) {}
        updateDensityUI();
        renderVaultUI();
    };

    const updateDensityUI = () => {
        const btnCard = document.getElementById('btn-density-card');
        const btnCompact = document.getElementById('btn-density-compact');
        if (btnCard && btnCompact) {
            if (currentDensity === 'compact') {
                btnCompact.className = 'p-1.5 rounded-lg text-xs font-medium transition bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-2xs';
                btnCard.className = 'p-1.5 rounded-lg text-xs font-medium transition text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
            } else {
                btnCard.className = 'p-1.5 rounded-lg text-xs font-medium transition bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-2xs';
                btnCompact.className = 'p-1.5 rounded-lg text-xs font-medium transition text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
            }
        }
    };

    /**
     * Mode Anti-Intip / Privacy Shield
     */
    const initPrivacyMode = () => {
        if (isPrivacyMode) {
            document.body.classList.add('privacy-blur-active');
        } else {
            document.body.classList.remove('privacy-blur-active');
        }
        updatePrivacyUI();
    };

    const togglePrivacyMode = () => {
        isPrivacyMode = !isPrivacyMode;
        try { localStorage.setItem('pv_privacy_mode', isPrivacyMode ? 'true' : 'false'); } catch (e) {}
        if (isPrivacyMode) {
            document.body.classList.add('privacy-blur-active');
            AppToast.show('🙈 Mode Anti-Intip Aktif: Data sensitif disamarkan di layar.', 'info', 2500);
        } else {
            document.body.classList.remove('privacy-blur-active');
            AppToast.show('👁️ Mode Anti-Intip Nonaktif: Tampilan normal.', 'info', 2000);
        }
        updatePrivacyUI();
    };

    const updatePrivacyUI = () => {
        const iconOn = document.getElementById('icon-privacy-on');
        const iconOff = document.getElementById('icon-privacy-off');
        const btn = document.getElementById('btn-privacy-toggle');
        if (iconOn && iconOff) {
            if (isPrivacyMode) {
                iconOn.classList.remove('hidden');
                iconOff.classList.add('hidden');
                if (btn) btn.classList.add('bg-blue-50', 'dark:bg-blue-950/40', 'text-blue-600');
            } else {
                iconOn.classList.add('hidden');
                iconOff.classList.remove('hidden');
                if (btn) btn.classList.remove('bg-blue-50', 'dark:bg-blue-950/40', 'text-blue-600');
            }
        }
    };

    /**
     * Terapkan Template Item Siap Pakai
     */
    const applyTemplate = (type) => {
        if (type === 'google') {
            openAddItemModal('login');
            setTimeout(() => {
                const nameInput = document.getElementById('item-name');
                const urlInput = document.getElementById('login-url');
                if (nameInput) nameInput.value = 'Akun Google';
                if (urlInput) urlInput.value = 'https://accounts.google.com';
                document.getElementById('login-username')?.focus();
            }, 60);
        } else if (type === 'github') {
            openAddItemModal('login');
            setTimeout(() => {
                const nameInput = document.getElementById('item-name');
                const urlInput = document.getElementById('login-url');
                if (nameInput) nameInput.value = 'GitHub';
                if (urlInput) urlInput.value = 'https://github.com';
                document.getElementById('login-username')?.focus();
            }, 60);
        }
    };

    return {
        unlockWithMasterPassword,
        syncAndDecrypt,
        renderVaultUI,
        showDetailModal,
        openEditItemModal,
        toggleDetailPassword,
        handleDetailToggleFav,
        copyToClipboard,
        saveItem,
        deleteItem,
        toggleFavorite,
        setFilter,
        setFolder,
        lockVault,
        setupActivityListeners,
        escapeHtml,
        escapeJs,
        getDecryptedItems: () => decryptedItems,
        getDecryptedFolders: () => decryptedFolders,
        getVaultKey: () => activeVaultKey,
        setDirectVaultKey: async (vaultKey) => {
            activeVaultKey = vaultKey;
            await saveSessionKey(vaultKey);
            resetAutoLock();
        },
        tryRestoreSession,
        isUnlocked: () => activeVaultKey !== null,
        createSecretShareLink,
        setDensity,
        togglePrivacyMode,
        initPrivacyMode,
        applyTemplate,
        downloadAttachment,
        resetAutoLock,
        setAutoLockMinutes: (mins) => {
            localStorage.setItem('pv_autolock_minutes', mins);
            resetAutoLock();
        }
    };
})();
