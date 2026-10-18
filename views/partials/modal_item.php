<!-- Modal Tambah / Edit Item Brankas -->
<div id="item-modal" class="modal-backdrop fixed inset-0 z-40 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-xl w-full p-5 sm:p-6 shadow-2xl relative my-auto sm:my-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
            <h3 id="item-modal-title" class="text-base font-bold text-gray-900 dark:text-white flex items-center">
                <span>Tambah Item Baru ke Brankas</span>
            </h3>
            <button type="button" onclick="closeModal('item-modal')" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="item-form" onsubmit="handleItemFormSubmit(event)" class="mt-4 space-y-4">
            <input type="hidden" id="item-id" value="">
            <input type="hidden" id="item-is-favorite" value="0">

            <!-- Tipe Item Tabs -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Tipe Item</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setItemType('login')" id="type-btn-login" class="type-selector-btn py-2 text-xs font-medium rounded-lg border text-center transition bg-blue-50 text-blue-700 border-blue-500 dark:bg-blue-900/40 dark:text-blue-300">Login</button>
                    <button type="button" onclick="setItemType('card')" id="type-btn-card" class="type-selector-btn py-2 text-xs font-medium rounded-lg border text-center transition border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Kartu</button>
                    <button type="button" onclick="setItemType('note')" id="type-btn-note" class="type-selector-btn py-2 text-xs font-medium rounded-lg border text-center transition border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Catatan</button>
                    <button type="button" onclick="setItemType('identity')" id="type-btn-identity" class="type-selector-btn py-2 text-xs font-medium rounded-lg border text-center transition border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Identitas</button>
                </div>
                <input type="hidden" id="item-type" value="login">
            </div>

            <!-- Nama Item & Folder -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="item-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Item *</label>
                    <input type="text" id="item-name" required placeholder="misal: Akun Google, BCA, Server VPS" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                </div>
                <div>
                    <label for="item-folder-id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Folder / Kategori</label>
                    <select id="item-folder-id" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                        <option value="">-- Tanpa Folder --</option>
                    </select>
                </div>
            </div>

            <!-- Fields Khusus: LOGIN -->
            <div id="fields-login" class="space-y-3">
                <div>
                    <label for="login-username" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Username / Email</label>
                    <input type="text" id="login-username" placeholder="user@example.com" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="login-password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Kata Sandi</label>
                        <button type="button" onclick="generateAndFillPassword()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Buat Sandi Kuat</span>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" id="login-password" oninput="updateItemPasswordStrength(this.value)" placeholder="••••••••••••" class="w-full pl-3.5 pr-20 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center space-x-1">
                            <button type="button" onclick="togglePasswordVisibility('login-password')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Lihat/Sembunyikan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Password Strength Bar -->
                    <div class="mt-1.5 flex items-center space-x-2">
                        <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div id="item-pwd-strength-bar" class="h-full bg-gray-400 transition-all duration-300 w-0"></div>
                        </div>
                        <span id="item-pwd-strength-text" class="text-[11px] font-medium text-gray-400">Kosong</span>
                    </div>
                </div>

                <div>
                    <label for="login-url" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Situs Web / URL</label>
                    <input type="url" id="login-url" placeholder="https://..." class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="login-totp" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kunci Rahasia TOTP 2FA (Opsional)</label>
                    <input type="text" id="login-totp" placeholder="JBSWY3DPEHPK3PXP" class="w-full px-3.5 py-2 text-sm font-mono uppercase bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                </div>
            </div>

            <!-- Fields Khusus: CARD -->
            <div id="fields-card" class="space-y-3 hidden">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="card-holder" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pemilik Kartu</label>
                        <input type="text" id="card-holder" placeholder="NAMA LENGKAP" class="w-full px-3.5 py-2 text-sm uppercase bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="card-brand" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Brand Kartu</label>
                        <select id="card-brand" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                            <option value="JCB">JCB</option>
                            <option value="American Express">American Express</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="card-number" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor Kartu</label>
                    <input type="text" id="card-number" placeholder="4000 1234 5678 9010" class="w-full px-3.5 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="card-exp-month" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                        <input type="text" id="card-exp-month" placeholder="12" maxlength="2" class="w-full px-3.5 py-2 text-sm font-mono text-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="card-exp-year" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <input type="text" id="card-exp-year" placeholder="2028" maxlength="4" class="w-full px-3.5 py-2 text-sm font-mono text-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="card-cvv" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">CVV</label>
                        <input type="password" id="card-cvv" placeholder="•••" maxlength="4" class="w-full px-3.5 py-2 text-sm font-mono text-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Fields Khusus: NOTE -->
            <div id="fields-note" class="space-y-3 hidden">
                <div>
                    <label for="note-content" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Isi Catatan Rahasia</label>
                    <textarea id="note-content" rows="6" placeholder="Kunci privat SSH, seed recovery 12 kata, PIN ATM..." class="w-full px-3.5 py-2.5 text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white"></textarea>
                </div>
            </div>

            <!-- Fields Khusus: IDENTITY -->
            <div id="fields-identity" class="space-y-3 hidden">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="identity-fullname" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input type="text" id="identity-fullname" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="identity-idnumber" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nomor KTP / Paspor</label>
                        <input type="text" id="identity-idnumber" class="w-full px-3.5 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label for="identity-address" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                    <textarea id="identity-address" rows="2" class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white"></textarea>
                </div>
            </div>

            <!-- Catatan Tambahan (Opsional) untuk selain tipe note -->
            <div id="common-notes-field">
                <label for="common-notes" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Terenkripsi)</label>
                <textarea id="common-notes" rows="2" placeholder="Informasi tambahan terkait item ini..." class="w-full px-3.5 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition text-gray-900 dark:text-white"></textarea>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <button type="button" onclick="closeModal('item-modal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition">Batal</button>
                <button type="submit" id="save-item-submit-btn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                    <span>Simpan ke Brankas</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setItemType(type) {
        document.getElementById('item-type').value = type;
        document.querySelectorAll('.type-selector-btn').forEach(b => {
            b.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-500', 'dark:bg-blue-900/40', 'dark:text-blue-300');
            b.classList.add('border-gray-200', 'dark:border-gray-700', 'text-gray-600', 'dark:text-gray-400');
        });
        const activeBtn = document.getElementById(`type-btn-${type}`);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-500', 'dark:bg-blue-900/40', 'dark:text-blue-300');
            activeBtn.classList.remove('border-gray-200', 'dark:border-gray-700', 'text-gray-600', 'dark:text-gray-400');
        }

        // Tampilkan field sesuai tipe
        ['login', 'card', 'note', 'identity'].forEach(t => {
            const el = document.getElementById(`fields-${t}`);
            if (el) el.classList.toggle('hidden', t !== type);
        });

        // Sembunyikan common notes jika tipenya adalah note
        const commonNotes = document.getElementById('common-notes-field');
        if (commonNotes) commonNotes.classList.toggle('hidden', type === 'note');
    }

    function openAddItemModal(type = 'login') {
        document.getElementById('item-modal-title').textContent = 'Tambah Item Baru ke Brankas';
        document.getElementById('item-form').reset();
        document.getElementById('item-id').value = '';
        document.getElementById('item-is-favorite').value = '0';
        setItemType(type);
        openModal('item-modal');
    }

    function generateAndFillPassword() {
        const pwd = PasswordGenerator.generatePassword({ length: 22 });
        const input = document.getElementById('login-password');
        input.value = pwd;
        input.type = 'text'; // Tampilkan sementara agar user melihatnya
        updateItemPasswordStrength(pwd);
        AppToast.show('Kata sandi acak dibuat!', 'info');
    }

    function updateItemPasswordStrength(pwd) {
        const bar = document.getElementById('item-pwd-strength-bar');
        const text = document.getElementById('item-pwd-strength-text');
        const res = PasswordGenerator.evaluateStrength(pwd);

        bar.className = `h-full transition-all duration-300 ${res.color}`;
        bar.style.width = res.width;
        text.textContent = res.label;
        text.className = `text-[11px] font-medium ${res.text || 'text-gray-400'}`;
    }

    async function handleItemFormSubmit(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('save-item-submit-btn');
        submitBtn.disabled = true;

        const id = document.getElementById('item-id').value;
        const type = document.getElementById('item-type').value;
        const name = document.getElementById('item-name').value;
        const folderId = document.getElementById('item-folder-id').value;
        const isFav = document.getElementById('item-is-favorite').value === '1';

        const payload = {
            name: name,
            type: type
        };

        if (type === 'login') {
            payload.username = document.getElementById('login-username').value;
            payload.password = document.getElementById('login-password').value;
            payload.url = document.getElementById('login-url').value;
            payload.totp_seed = document.getElementById('login-totp').value;
            payload.notes = document.getElementById('common-notes').value;
        } else if (type === 'card') {
            payload.cardholder = document.getElementById('card-holder').value;
            payload.brand = document.getElementById('card-brand').value;
            payload.number = document.getElementById('card-number').value;
            payload.exp_month = document.getElementById('card-exp-month').value;
            payload.exp_year = document.getElementById('card-exp-year').value;
            payload.cvv = document.getElementById('card-cvv').value;
            payload.notes = document.getElementById('common-notes').value;
        } else if (type === 'note') {
            payload.notes = document.getElementById('note-content').value;
        } else if (type === 'identity') {
            payload.full_name = document.getElementById('identity-fullname').value;
            payload.id_number = document.getElementById('identity-idnumber').value;
            payload.address = document.getElementById('identity-address').value;
            payload.notes = document.getElementById('common-notes').value;
        }

        await VaultApp.saveItem({
            id: id,
            folder_id: folderId,
            item_type: type,
            is_favorite: isFav,
            payload: payload
        });

        submitBtn.disabled = false;
    }
</script>
