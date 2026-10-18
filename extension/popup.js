/**
 * PassVault Companion — Popup Script
 */

let generatedPass = '';
let itemsCache = [];

document.addEventListener('DOMContentLoaded', async () => {
    initGenerator();
    initSearch();
    await loadCurrentTabInfo();
    await tryFetchItems();
});

function generatePassword(length = 18, useSpecial = true) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' + (useSpecial ? '!@#$%^&*()_+-=[]{}|;:,.<>?' : '');
    const array = new Uint32Array(length);
    crypto.getRandomValues(array);
    let pass = '';
    for (let i = 0; i < length; i++) {
        pass += chars[array[i] % chars.length];
    }
    return pass;
}

function initGenerator() {
    const genResult = document.getElementById('ext-gen-result');
    const btnGen = document.getElementById('ext-btn-gen');
    const btnCopyGen = document.getElementById('ext-btn-copy-gen');

    const refreshPass = () => {
        generatedPass = generatePassword(18);
        if (genResult) genResult.textContent = generatedPass;
    };

    refreshPass();

    if (btnGen) {
        btnGen.addEventListener('click', refreshPass);
    }

    if (btnCopyGen) {
        btnCopyGen.addEventListener('click', async () => {
            if (!generatedPass) return;
            await navigator.clipboard.writeText(generatedPass);
            const orig = btnCopyGen.textContent;
            btnCopyGen.textContent = 'Tersalin!';
            btnCopyGen.style.background = '#10b981';
            setTimeout(() => {
                btnCopyGen.textContent = orig;
                btnCopyGen.style.background = '';
            }, 1500);
        });
    }
}

async function loadCurrentTabInfo() {
    try {
        const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
        if (tab && tab.url) {
            try {
                const urlObj = new URL(tab.url);
                const hostname = urlObj.hostname;
                const searchInput = document.getElementById('ext-search');
                if (searchInput && hostname && !hostname.includes('localhost') && !hostname.includes('127.0.0.1')) {
                    const cleanDomain = hostname.replace(/^www\./, '');
                    searchInput.placeholder = `Cari untuk ${cleanDomain}...`;
                }
            } catch (e) {}
        }
    } catch (e) {}
}

async function tryFetchItems() {
    const listEl = document.getElementById('ext-item-list');
    if (!listEl) return;

    // Check if user is logged into PassVault
    try {
        const res = await fetch('http://localhost/pwd/api/items', { credentials: 'include' });
        if (!res.ok) {
            renderLoggedOut(listEl);
            return;
        }
        const data = await res.json();
        if (data.success && Array.isArray(data.items)) {
            itemsCache = data.items;
            renderItems(itemsCache);
        } else {
            renderLoggedOut(listEl);
        }
    } catch (err) {
        renderLoggedOut(listEl);
    }
}

function renderLoggedOut(listEl) {
    listEl.innerHTML = `
        <div class="empty-hint">
            <p style="margin-bottom: 8px;">Brankas terkunci atau belum dibuka.</p>
            <a href="http://localhost/pwd/vault" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 11px;">
                Buka Tab PassVault ↗
            </a>
        </div>
    `;
}

function renderItems(items) {
    const listEl = document.getElementById('ext-item-list');
    if (!listEl) return;

    if (items.length === 0) {
        listEl.innerHTML = `<div class="empty-hint">Tidak ada akun yang tersimpan.</div>`;
        return;
    }

    listEl.innerHTML = '';
    items.forEach(item => {
        // Fallback display if client-side decrypted or encrypted preview
        const name = item.name || item.title || 'Akun Tersimpan';
        const user = item.username || item.email || (item.category ? `Kategori: ${item.category}` : 'PassVault Item');

        const card = document.createElement('div');
        card.className = 'item-card';
        card.innerHTML = `
            <div class="item-info">
                <div class="item-name">${escapeHtml(name)}</div>
                <div class="item-user">${escapeHtml(user)}</div>
            </div>
            <div class="item-actions">
                <button type="button" class="btn-act btn-fill autofill-btn">Isi Form</button>
            </div>
        `;

        const autofillBtn = card.querySelector('.autofill-btn');
        autofillBtn.addEventListener('click', async () => {
            await triggerAutofill(item);
            autofillBtn.textContent = '✓ Terisi';
            setTimeout(() => { autofillBtn.textContent = 'Isi Form'; }, 1500);
        });

        listEl.appendChild(card);
    });
}

async function triggerAutofill(item) {
    try {
        const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
        if (!tab || !tab.id) return;

        // Decrypted data if present, or open PassVault item
        const username = item.username || '';
        const password = item.password || '';

        if (!password) {
            // Focus or open PassVault tab to unlock item
            chrome.tabs.create({ url: `http://localhost/pwd/vault?search=${encodeURIComponent(item.name || '')}` });
            return;
        }

        chrome.tabs.sendMessage(tab.id, {
            action: 'autofill',
            username: username,
            password: password
        });
    } catch (e) {
        console.error('Autofill error:', e);
    }
}

function initSearch() {
    const searchInput = document.getElementById('ext-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        if (!q) {
            renderItems(itemsCache);
            return;
        }
        const filtered = itemsCache.filter(it => {
            const name = (it.name || it.title || '').toLowerCase();
            const user = (it.username || it.email || '').toLowerCase();
            const url = (it.url || '').toLowerCase();
            return name.includes(q) || user.includes(q) || url.includes(q);
        });
        renderItems(filtered);
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
