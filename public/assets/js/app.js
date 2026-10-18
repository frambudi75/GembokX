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

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();

    // Tutup modal dengan tombol Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
        // Shortcut pencarian: Ctrl + K atau /
        if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA')) {
            e.preventDefault();
            const searchInput = document.getElementById('vault-search-input');
            if (searchInput) searchInput.focus();
        }
    });
});
