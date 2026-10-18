<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna & Anggota</h2>
                <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50 rounded">Admin Panel</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola akun brankas untuk tim atau rekan Anda dengan arsitektur privasi Zero-Knowledge.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?= $url('/vault') ?>" class="px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition border border-gray-200 dark:border-gray-700">
                Kembali ke Brankas
            </a>
            <button type="button" onclick="openAddUserModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xs">
            <span class="text-xs text-gray-400 block font-medium">Total Akun</span>
            <div class="flex items-center justify-between mt-2">
                <span id="stat-total-users" class="text-2xl font-bold text-gray-900 dark:text-white">-</span>
                <span class="p-2 bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xs">
            <span class="text-xs text-gray-400 block font-medium">Akun Aktif</span>
            <div class="flex items-center justify-between mt-2">
                <span id="stat-active-users" class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">-</span>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xs">
            <span class="text-xs text-gray-400 block font-medium">Administrator</span>
            <div class="flex items-center justify-between mt-2">
                <span id="stat-admin-users" class="text-2xl font-bold text-purple-600 dark:text-purple-400">-</span>
                <span class="p-2 bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xs">
            <span class="text-xs text-gray-400 block font-medium">Terlindungi 2FA</span>
            <div class="flex items-center justify-between mt-2">
                <span id="stat-2fa-users" class="text-2xl font-bold text-blue-600 dark:text-blue-400">-</span>
                <span class="p-2 bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Pengguna -->
    <div class="flex items-center justify-between gap-3">
        <div class="relative flex-1 max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="user-filter-input" placeholder="Cari berdasarkan nama, email, username..." oninput="filterUserTable(this.value)" class="w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-blue-500/50">
        </div>
        <button type="button" onclick="loadUsersList()" class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition border border-gray-200 dark:border-gray-700 flex items-center space-x-1.5" title="Refresh Daftar">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span class="hidden sm:inline">Refresh</span>
        </button>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Role / Peran</th>
                        <th class="py-3 px-4">Status Akun</th>
                        <th class="py-3 px-4">Keamanan</th>
                        <th class="py-3 px-4">Item Brankas</th>
                        <th class="py-3 px-4">Bergabung</th>
                        <th class="py-3 px-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="user-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">
                            <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Memuat data pengguna...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna Baru -->
<div id="modal-add-user" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tambah Pengguna / Rekan Baru</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Akun akan langsung dienkripsi dengan Master Password awal di browser.</p>
            </div>
            <button type="button" onclick="closeModal('modal-add-user')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="form-add-user" onsubmit="submitAddUser(event)" class="p-5 space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Email *</label>
                    <input type="email" id="new-user-email" required placeholder="teman@contoh.com" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pengguna (Username) *</label>
                    <input type="text" id="new-user-username" required placeholder="budi_s" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" id="new-user-fullname" placeholder="Budi Santoso" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">No. WhatsApp / Telepon</label>
                    <input type="text" id="new-user-phone" placeholder="08123456789" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Peran (Role)</label>
                <select id="new-user-role" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                    <option value="user">Pengguna Biasa (Hanya Brankas Sendiri)</option>
                    <option value="admin">Administrator (Bisa Kelola Pengguna)</option>
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-semibold text-gray-700 dark:text-gray-300">Master Password Awal *</label>
                    <button type="button" onclick="generateNewUserPassword()" class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] font-medium">Buat Sandi Acak</button>
                </div>
                <div class="relative">
                    <input type="text" id="new-user-password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full px-3 py-2 font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50">
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Berikan sandi ini kepada rekan Anda. Sandi ini akan langsung menghasilkan Kunci Brankas Zero-Knowledge.</p>
            </div>

            <div id="add-user-progress" class="hidden p-3 bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/40 rounded-xl text-blue-700 dark:text-blue-300 flex items-center space-x-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Menderivasi kunci kriptografi Zero-Knowledge di browser...</span>
            </div>

            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('modal-add-user')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Batal
                </button>
                <button type="submit" id="btn-save-new-user" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-xs transition">
                    Buat Akun Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Info Kredensial Berhasil Dibuat -->
<div id="modal-user-created-info" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-md w-full border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden p-6 space-y-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="text-center">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Akun Rekan Berhasil Dibuat!</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Salin kredensial berikut dan kirimkan ke rekan Anda.</p>
        </div>

        <div class="p-3.5 bg-gray-50 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 rounded-xl space-y-2 text-xs">
            <div>
                <span class="text-gray-400 block text-[10px]">Email Login:</span>
                <span id="created-info-email" class="font-semibold text-gray-900 dark:text-white font-mono"></span>
            </div>
            <div>
                <span class="text-gray-400 block text-[10px]">Master Password:</span>
                <span id="created-info-pass" class="font-semibold text-blue-600 dark:text-blue-400 font-mono"></span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="copyCreatedCredentials()" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                Salin Kredensial
            </button>
            <button type="button" onclick="closeModal('modal-user-created-info'); loadUsersList();" class="py-2 px-4 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let allUsersData = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadUsersList();
    });

    async function loadUsersList() {
        try {
            const res = await fetch(window.PV_CONFIG.routes.adminUserList);
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal mengambil data pengguna.');

            allUsersData = data.users || [];
            updateStats(allUsersData);
            renderUserTable(allUsersData);
        } catch (err) {
            AppToast.show(err.message, 'error');
            document.getElementById('user-table-body').innerHTML = `
                <tr><td colspan="7" class="py-6 text-center text-rose-500 font-medium">Gagal memuat daftar pengguna: ${err.message}</td></tr>
            `;
        }
    }

    function updateStats(users) {
        document.getElementById('stat-total-users').textContent = users.length;
        document.getElementById('stat-active-users').textContent = users.filter(u => parseInt(u.is_active) === 1).length;
        document.getElementById('stat-admin-users').textContent = users.filter(u => u.role === 'admin').length;
        document.getElementById('stat-2fa-users').textContent = users.filter(u => parseInt(u.totp_enabled) === 1).length;
    }

    function renderUserTable(users) {
        const tbody = document.getElementById('user-table-body');
        if (users.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-gray-400">Tidak ada pengguna ditemukan.</td></tr>`;
            return;
        }

        const currentUserId = <?= (int) ($user['id'] ?? 0) ?>;

        tbody.innerHTML = users.map(u => {
            const isActive = parseInt(u.is_active) === 1;
            const is2fa = parseInt(u.totp_enabled) === 1;
            const isAdmin = u.role === 'admin';
            const isSelf = parseInt(u.id) === currentUserId;

            const avatarMarkup = u.avatar 
                ? `<img src="${window.PV_CONFIG.baseUrl + u.avatar.replace(/^\//, '')}" class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="Avatar">`
                : `<div class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs uppercase">${(u.username || 'U')[0]}</div>`;

            const fullNameDisplay = u.full_name ? `<span class="font-bold text-gray-900 dark:text-white block">${escapeHtml(u.full_name)}</span>` : '';
            const phoneDisplay = u.phone ? `<span class="text-[10px] text-gray-400 block">${escapeHtml(u.phone)}</span>` : '';

            return `
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-3">
                            ${avatarMarkup}
                            <div>
                                ${fullNameDisplay}
                                <span class="font-medium text-gray-700 dark:text-gray-300">@${escapeHtml(u.username)}</span>
                                <span class="text-[11px] text-gray-400 block font-mono">${escapeHtml(u.email)}</span>
                                ${phoneDisplay}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        ${isAdmin 
                            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50">Administrator</span>`
                            : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">Pengguna</span>`
                        }
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        ${isActive
                            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Aktif</span>`
                            : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">Nonaktif</span>`
                        }
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        ${is2fa 
                            ? `<span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-medium space-x-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>2FA Aktif</span></span>`
                            : `<span class="text-gray-400">Tidak Ada</span>`
                        }
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        <span class="font-bold text-gray-900 dark:text-white">${u.items_count || 0}</span> <span class="text-gray-400">item</span>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap text-gray-400 text-[11px]">
                        ${formatDate(u.created_at)}
                    </td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        ${isSelf ? `
                            <span class="text-[11px] text-gray-400 italic">Akun Anda</span>
                        ` : `
                            <div class="flex items-center justify-end space-x-1.5">
                                <!-- Ubah Role -->
                                <button type="button" onclick="toggleUserRole(${u.id}, '${isAdmin ? 'user' : 'admin'}')" class="p-1.5 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition" title="Ubah Peran ke ${isAdmin ? 'Pengguna' : 'Admin'}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                </button>

                                <!-- Toggle Status -->
                                <button type="button" onclick="toggleUserStatus(${u.id}, ${isActive ? 0 : 1})" class="p-1.5 ${isActive ? 'text-amber-500 hover:text-amber-600' : 'text-emerald-500 hover:text-emerald-600'} hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition" title="${isActive ? 'Nonaktifkan Akun' : 'Aktifkan Akun'}">
                                    ${isActive ? `
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    ` : `
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    `}
                                </button>

                                <!-- Delete -->
                                <button type="button" onclick="deleteUserAccount(${u.id}, '${escapeHtml(u.username)}')" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition" title="Hapus Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        `}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterUserTable(query) {
        const q = (query || '').toLowerCase().trim();
        if (!q) {
            renderUserTable(allUsersData);
            return;
        }

        const filtered = allUsersData.filter(u => {
            return (u.email && u.email.toLowerCase().includes(q)) ||
                   (u.username && u.username.toLowerCase().includes(q)) ||
                   (u.full_name && u.full_name.toLowerCase().includes(q)) ||
                   (u.phone && u.phone.toLowerCase().includes(q));
        });

        renderUserTable(filtered);
    }

    function openAddUserModal() {
        document.getElementById('form-add-user').reset();
        document.getElementById('add-user-progress').classList.add('hidden');
        document.getElementById('btn-save-new-user').disabled = false;
        generateNewUserPassword();
        openModal('modal-add-user');
    }

    function generateNewUserPassword() {
        if (typeof PasswordGenerator !== 'undefined' && PasswordGenerator.generate) {
            const pass = PasswordGenerator.generate({ length: 14, uppercase: true, lowercase: true, numbers: true, symbols: true });
            document.getElementById('new-user-password').value = pass;
        } else {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
            let pass = '';
            for (let i = 0; i < 14; i++) pass += chars[Math.floor(Math.random() * chars.length)];
            document.getElementById('new-user-password').value = pass;
        }
    }

    async function submitAddUser(e) {
        e.preventDefault();
        const email = document.getElementById('new-user-email').value.trim();
        const username = document.getElementById('new-user-username').value.trim();
        const fullName = document.getElementById('new-user-fullname').value.trim();
        const phone = document.getElementById('new-user-phone').value.trim();
        const role = document.getElementById('new-user-role').value;
        const password = document.getElementById('new-user-password').value;

        if (!email || !username || !password) {
            AppToast.show('Email, Username, dan Sandi Awal wajib diisi.', 'error');
            return;
        }

        const progressEl = document.getElementById('add-user-progress');
        const submitBtn = document.getElementById('btn-save-new-user');

        try {
            progressEl.classList.remove('hidden');
            submitBtn.disabled = true;

            // Enkripsi Zero-Knowledge Klien
            const salt = VaultCrypto.generateSalt(16);
            const iterations = 600000;
            const masterKey = await VaultCrypto.deriveMasterKey(password, salt, iterations);
            const authKey = await VaultCrypto.deriveAuthKey(masterKey);
            const vaultKey = await VaultCrypto.generateVaultKey();
            const encryptedVault = await VaultCrypto.encryptVaultKey(vaultKey, masterKey);
            const recoveryCode = VaultCrypto.generateRecoveryCode();

            const payload = {
                email: email,
                username: username,
                full_name: fullName,
                phone: phone,
                role: role,
                auth_key: authKey,
                kdf_salt: salt,
                kdf_iterations: iterations,
                encrypted_vault_key: encryptedVault.encryptedKey,
                vault_key_iv: encryptedVault.iv,
                recovery_code: recoveryCode,
                csrf_token: window.PV_CONFIG.csrfToken
            };

            const res = await fetch(window.PV_CONFIG.routes.adminUserCreate, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal menambahkan pengguna.');

            closeModal('modal-add-user');

            // Tampilkan modal kredensial
            document.getElementById('created-info-email').textContent = email;
            document.getElementById('created-info-pass').textContent = password;
            openModal('modal-user-created-info');

            AppToast.show(data.message, 'success');
        } catch (err) {
            AppToast.show(err.message, 'error');
        } finally {
            progressEl.classList.add('hidden');
            submitBtn.disabled = false;
        }
    }

    function copyCreatedCredentials() {
        const email = document.getElementById('created-info-email').textContent;
        const pass = document.getElementById('created-info-pass').textContent;
        const text = `Akun GembokX Anda:\nEmail: ${email}\nMaster Password: ${pass}\nURL: ${window.location.origin + window.PV_CONFIG.baseUrl}\n\nCatatan: Segera ubah Master Password setelah login pertama kali.`;
        VaultApp.copyToClipboard(text, 'Kredensial disalin ke clipboard!');
    }

    async function toggleUserStatus(userId, nextStatus) {
        const actionLabel = nextStatus === 1 ? 'mengaktifkan' : 'menonaktifkan';
        if (!confirm(`Apakah Anda yakin ingin ${actionLabel} akun pengguna ini?`)) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.adminUserToggleStatus, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    id: userId,
                    is_active: nextStatus,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            loadUsersList();
        } catch (err) {
            AppToast.show(err.message, 'error');
        }
    }

    async function toggleUserRole(userId, newRole) {
        if (!confirm(`Ubah peran pengguna ini menjadi ${newRole === 'admin' ? 'Administrator' : 'Pengguna Biasa'}?`)) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.adminUserChangeRole, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    id: userId,
                    role: newRole,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            loadUsersList();
        } catch (err) {
            AppToast.show(err.message, 'error');
        }
    }

    async function deleteUserAccount(userId, username) {
        if (!confirm(`PERINGATAN: Menghapus akun '${username}' akan melenyapkan seluruh data brankas milik pengguna ini secara permanen. Lanjutkan?`)) return;

        try {
            const res = await fetch(window.PV_CONFIG.routes.adminUserDelete, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.PV_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    id: userId,
                    csrf_token: window.PV_CONFIG.csrfToken
                })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            AppToast.show(data.message, 'success');
            loadUsersList();
        } catch (err) {
            AppToast.show(err.message, 'error');
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }
</script>
