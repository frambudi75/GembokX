/**
 * PassVault — UI Interaction, Modals, Toast & Theme Engine
 */

// Toast Notification Manager
const AppToast = (() => {
    'use strict';

    const show = (message, type = 'info', duration = 3500) => {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-center px-4 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all transform duration-300 translate-y-3 opacity-0 `;

        if (type === 'success') {
            toast.className += 'bg-emerald-900/90 text-emerald-100 border-emerald-700/60 shadow-emerald-950/40';
            toast.innerHTML = `<svg class="w-5 h-5 mr-2.5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>${message}</span>`;
        } else if (type === 'error') {
            toast.className += 'bg-rose-900/90 text-rose-100 border-rose-700/60 shadow-rose-950/40';
            toast.innerHTML = `<svg class="w-5 h-5 mr-2.5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg><span>${message}</span>`;
        } else {
            toast.className += 'bg-gray-900/90 text-gray-100 border-gray-700/60 shadow-black/40';
            toast.innerHTML = `<svg class="w-5 h-5 mr-2.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span>${message}</span>`;
        }

        container.appendChild(toast);

        // Animasi Masuk
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-3', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Hapus otomatis
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-3', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    return { show };
})();

// Modal Helper Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Dark Mode Toggle
const ThemeManager = (() => {
    'use strict';

    const init = () => {
        const saved = localStorage.getItem('pv_theme');
        if (saved === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            // Default dark mode untuk aplikasi password manager (ramah mata & elegan)
            document.documentElement.classList.add('dark');
        }
    };

    const toggle = () => {
        const isDark = document.documentElement.classList.contains('dark');
        if (isDark) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('pv_theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('pv_theme', 'dark');
        }
    };

    return { init, toggle };
})();

// Dynamic Password Strength & Entropy Calculator
function updateItemPasswordStrength(password) {
    const bar = document.getElementById('item-pwd-strength-bar');
    const text = document.getElementById('item-pwd-strength-text');
    if (!bar || !text) return;

    if (!password || password.length === 0) {
        bar.style.width = '0%';
        bar.className = 'h-full bg-gray-400 transition-all duration-300 w-0';
        text.textContent = 'Kosong';
        text.className = 'text-[11px] font-medium text-gray-400';
        return;
    }

    let charsetSize = 0;
    if (/[a-z]/.test(password)) charsetSize += 26;
    if (/[A-Z]/.test(password)) charsetSize += 26;
    if (/[0-9]/.test(password)) charsetSize += 10;
    if (/[^a-zA-Z0-9]/.test(password)) charsetSize += 33;

    const entropy = Math.round(password.length * Math.log2(Math.max(charsetSize, 2)));

    if (entropy < 35 || password.length < 8) {
        bar.style.width = '25%';
        bar.className = 'h-full bg-rose-500 transition-all duration-300';
        text.textContent = `Sangat Lemah (${entropy} bits • crack < 1 detik)`;
        text.className = 'text-[11px] font-semibold text-rose-500';
    } else if (entropy < 55 || password.length < 12) {
        bar.style.width = '50%';
        bar.className = 'h-full bg-amber-500 transition-all duration-300';
        text.textContent = `Sedang (${entropy} bits • crack ~minggu/bulan)`;
        text.className = 'text-[11px] font-semibold text-amber-500';
    } else if (entropy < 75) {
        bar.style.width = '75%';
        bar.className = 'h-full bg-blue-500 transition-all duration-300';
        text.textContent = `Kuat (${entropy} bits • crack ~ratusan tahun)`;
        text.className = 'text-[11px] font-semibold text-blue-500';
    } else {
        bar.style.width = '100%';
        bar.className = 'h-full bg-emerald-500 transition-all duration-300';
        text.textContent = `Sangat Kuat (${entropy} bits • astronomis)`;
        text.className = 'text-[11px] font-semibold text-emerald-500';
    }
}

// SecurityShield: Anti-Tamper, Anti-Inspect & DevTools Hardening
const SecurityShield = (() => {
    'use strict';

    let devToolsOpened = false;
    let idleTimer = null;
    const IDLE_TIMEOUT_MS = 5 * 60 * 1000; // 5 Menit Inactivity Auto-Lock

    const resetIdleTimer = () => {
        if (idleTimer) clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            if (typeof VaultApp !== 'undefined' && typeof VaultApp.lockVault === 'function') {
                AppToast.show('Brankas terkunci otomatis karena tidak ada aktivitas.', 'info');
                VaultApp.lockVault();
            }
        }, IDLE_TIMEOUT_MS);
    };

    const init = () => {
        // 1. Blokir Menu Klik Kanan pada elemen antarmuka (kecuali saat mengetik di input text/textarea)
        document.addEventListener('contextmenu', (e) => {
            const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
            if (!isInput) {
                e.preventDefault();
                AppToast.show('🔒 Menu klik kanan dinonaktifkan demi perlindungan data.', 'info', 2000);
            }
        });

        // 2. Blokir Tombol Pintasan Inspect Element (F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U)
        document.addEventListener('keydown', (e) => {
            const isInspectKey = 
                e.key === 'F12' ||
                ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c'].includes(e.key.toLowerCase())) ||
                ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'u');

            if (isInspectKey) {
                e.preventDefault();
                AppToast.show('🛡️ Akses Inspect Element dicegah oleh kebijakan keamanan.', 'error', 2500);
            }

            resetIdleTimer();
        });

        // 3. Deteksi Pembukaan DevTools via Perubahan Dimensi Jendela
        const threshold = 160;
        setInterval(() => {
            const widthDiff = window.outerWidth - window.innerWidth > threshold;
            const heightDiff = window.outerHeight - window.innerHeight > threshold;
            if ((widthDiff || heightDiff) && !devToolsOpened) {
                devToolsOpened = true;
                // Aktifkan mode anti-intip otomatis untuk melindungi kredensial
                document.body.classList.add('privacy-blur-active');
                AppToast.show('⚠️ DevTools terdeteksi. Informasi sensitif disamarkan demi keamanan.', 'error', 4000);
            } else if (!widthDiff && !heightDiff && devToolsOpened) {
                devToolsOpened = false;
                const savedPrivacy = localStorage.getItem('pv_privacy_mode');
                if (savedPrivacy !== 'true') {
                    document.body.classList.remove('privacy-blur-active');
                }
            }
        }, 1500);

        // 4. Inactivity Listener
        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
            window.addEventListener(evt, resetIdleTimer, { passive: true });
        });
        resetIdleTimer();
    };

    return { init };
})();

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
    SecurityShield.init();

    // Tutup modal dengan tombol Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }

        const isInput = document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA' || document.activeElement.isContentEditable);

        // Shortcut pencarian: Ctrl + K atau /
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('vault-search-input');
            const mobSearch = document.getElementById('mobile-vault-search-input');
            if (searchInput && searchInput.offsetParent !== null) {
                searchInput.focus();
            } else if (mobSearch) {
                document.getElementById('mobile-search-bar')?.classList.remove('hidden');
                mobSearch.focus();
            }
        }

        // Quick action: N (New item) dan G (Generator) saat tidak sedang mengetik
        if (!isInput && !e.ctrlKey && !e.metaKey && !e.altKey) {
            if (e.key.toLowerCase() === 'n') {
                e.preventDefault();
                if (typeof openAddItemModal === 'function') openAddItemModal('login');
            } else if (e.key.toLowerCase() === 'g') {
                e.preventDefault();
                if (typeof openModal === 'function') openModal('generator-modal');
            }
        }
    });
});

