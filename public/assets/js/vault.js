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
    const IDLE_TIMEOUT_MS = 15 * 60 * 1000; // 15 Menit

    /**
     * Inisialisasi Auto-Lock Timer
     */
    const resetAutoLock = () => {
        if (autoLockTimer) clearTimeout(autoLockTimer);
        autoLockTimer = setTimeout(() => {
            lockVault();
        }, IDLE_TIMEOUT_MS);
    };

    const setupActivityListeners = () => {
        ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(evt => {
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
     * Buka Kunci Brankas dengan Master Password
     */
    const unlockWithMasterPassword = async (masterPassword, encryptedKeyB64, ivB64, saltHex, iterations) => {
        try {
            const masterKey = await VaultCrypto.deriveMasterKey(masterPassword, saltHex, iterations);
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

        container.innerHTML = filtered.map(item => {
            const d = item.data;
            const isFav = item.is_favorite;
            const typeIcons = {
                login: `<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>`,
                card: `<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>`,
                note: `<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>`,
                identity: `<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>`
            };

            const icon = typeIcons[item.item_type] || typeIcons.login;

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

            return `
            <div class="group flex items-center justify-between p-3.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl transition duration-150 shadow-xs cursor-pointer" onclick="VaultApp.showDetailModal(${item.id})">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div class="p-2.5 bg-gray-100 dark:bg-gray-700 rounded-lg flex-shrink-0">
                        ${icon}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">${escapeHtml(d.name || 'Tanpa Judul')}</h4>
                            ${folderTag}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">${escapeHtml(subtext)}</p>
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
                <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${escapeHtml(d.notes)}</p>
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

    function renderDetailField(label, val, canCopy = false, isMono = false) {
        return `
        <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-800">
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">${escapeHtml(label)}</span>
            <div class="flex items-center justify-between">
                <span class="text-sm ${isMono ? 'font-mono' : ''} text-gray-900 dark:text-gray-100 truncate">${escapeHtml(val)}</span>
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
                <span id="${randId}" data-real="${escapeHtml(pwd)}" class="text-sm font-mono text-gray-900 dark:text-gray-100 truncate">••••••••••••</span>
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
        createSecretShareLink
    };
})();
