<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span>Audit Keamanan Sandi Brankas</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemeriksaan kesehatan kata sandi 100% lokal di browser tanpa mengirim password ke server.</p>
        </div>
        <a href="<?= $url('/vault') ?>" class="px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition border border-gray-200 dark:border-gray-700">
            Kembali ke Brankas
        </a>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Total Passwords -->
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Sandi</span>
            <div id="audit-total-pwd" class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">0</div>
            <p class="text-[10px] text-gray-400 mt-0.5">Seluruh kredensial</p>
        </div>

        <!-- Weak Passwords -->
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider block">Sandi Lemah</span>
            <div id="audit-weak-pwd" class="text-2xl font-extrabold text-rose-500 mt-1">0</div>
            <p class="text-[10px] text-gray-400 mt-0.5">&lt; 12 karakter / pola mudah</p>
        </div>

        <!-- Reused Passwords -->
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-amber-500 uppercase tracking-wider block">Sandi Berulang</span>
            <div id="audit-reused-pwd" class="text-2xl font-extrabold text-amber-500 mt-1">0</div>
            <p class="text-[10px] text-gray-400 mt-0.5">Digunakan di akun lain</p>
        </div>

        <!-- Pwned / Breached Passwords -->
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider block">Bocor Dark Web</span>
            <div id="audit-breached-pwd" class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1">-</div>
            <p class="text-[10px] text-gray-400 mt-0.5">HaveIBeenPwned</p>
        </div>

        <!-- Aged Passwords -->
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xs col-span-2 sm:col-span-1">
            <span class="text-xs font-semibold text-orange-500 uppercase tracking-wider block">Sandi Usang</span>
            <div id="audit-aged-pwd" class="text-2xl font-extrabold text-orange-500 mt-1">0</div>
            <p class="text-[10px] text-gray-400 mt-0.5">&gt; 180 hari tidak diganti</p>
        </div>
    </div>

    <!-- Kartu Edukasi Sumber Data & Metodologi Audit -->
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-950/20 dark:to-purple-950/20 border border-blue-200/80 dark:border-blue-900/40 rounded-2xl p-5 shadow-xs">
        <div class="flex items-start space-x-3.5">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="flex-1 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center space-x-1.5">
                        <span>Penjelasan Sumber Data & Indikator Keamanan</span>
                    </h3>
                    <span class="text-[11px] font-medium text-blue-700 dark:text-blue-300">100% Zero-Knowledge • Sandi Tidak Pernah Dikirim</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-gray-600 dark:text-gray-300">
                    <div class="bg-white/80 dark:bg-gray-800/80 p-3.5 rounded-xl border border-blue-100 dark:border-gray-700 shadow-2xs space-y-1">
                        <div class="font-bold text-purple-700 dark:text-purple-400 flex items-center space-x-1.5">
                            <span>🌐 Sumber Dark Web</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                            Berasal dari <strong>HaveIBeenPwned (HIBP)</strong>, database yang memuat 900+ juta sandi dari kasus peretasan besar dunia nyata (Adobe, LinkedIn, Canva, hingga log malware stealer).
                        </p>
                    </div>

                    <div class="bg-white/80 dark:bg-gray-800/80 p-3.5 rounded-xl border border-blue-100 dark:border-gray-700 shadow-2xs space-y-1">
                        <div class="font-bold text-rose-700 dark:text-rose-400 flex items-center space-x-1.5">
                            <span>🔑 Apa Yang Dinilai "Lemah"?</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                            Yang dinilai adalah <strong>KATA SANDI</strong> (bukan username). Sandi pendek (&lt; 12 karakter) sangat mudah ditebak penyerang dengan kartu grafis modern (brute-force).
                        </p>
                    </div>

                    <div class="bg-white/80 dark:bg-gray-800/80 p-3.5 rounded-xl border border-blue-100 dark:border-gray-700 shadow-2xs space-y-1">
                        <div class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center space-x-1.5">
                            <span>🛡️ Privasi k-Anonymity</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                            Sandi di-hash SHA-1 di browser. Hanya 5 karakter awal hash yang dikirim untuk mencocokkan. Pihak luar tidak pernah tahu apa kata sandi asli Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breach Check Action Bar & Filter Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <button type="button" onclick="filterAuditList('all')" id="audit-filter-all" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 transition">Semua Masalah</button>
            <button type="button" onclick="filterAuditList('weak')" id="audit-filter-weak" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Sandi Lemah</button>
            <button type="button" onclick="filterAuditList('reused')" id="audit-filter-reused" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Sandi Berulang</button>
            <button type="button" onclick="filterAuditList('breached')" id="audit-filter-breached" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Bocor di Dark Web</button>
            <button type="button" onclick="filterAuditList('aged')" id="audit-filter-aged" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Sandi Usang (&gt; 6 Bln)</button>
        </div>

        <div>
            <button type="button" id="btn-check-hibp" onclick="checkAllPasswordsAgainstHibp()" class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span>Cek Kebocoran Dark Web (k-Anonymity)</span>
            </button>
        </div>
    </div>

    <!-- Audit Items Table / List -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-xs">
        <div id="audit-list-container" class="divide-y divide-gray-100 dark:divide-gray-800">
            <div class="p-8 text-center text-sm text-gray-400">
                <p>Memuat dan menganalisis keamanan sandi...</p>
            </div>
        </div>
    </div>
</div>

<script>
    let auditItemsData = [];
    let currentAuditFilter = 'all';
    let breachedCache = {};

    async function checkPwnedPassword(password) {
        if (!password) return 0;
        if (breachedCache[password] !== undefined) return breachedCache[password];

        try {
            const enc = new TextEncoder();
            const digest = await window.crypto.subtle.digest('SHA-1', enc.encode(password));
            const hash = Array.from(new Uint8Array(digest))
                .map(b => b.toString(16).padStart(2, '0'))
                .join('')
                .toUpperCase();

            const prefix = hash.slice(0, 5);
            const suffix = hash.slice(5);

            const res = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);
            if (!res.ok) return 0;

            const text = await res.text();
            const lines = text.split('\n');
            let count = 0;
            for (const line of lines) {
                const [lineSuffix, lineCount] = line.trim().split(':');
                if (lineSuffix === suffix) {
                    count = parseInt(lineCount, 10);
                    break;
                }
            }

            breachedCache[password] = count;
            return count;
        } catch (e) {
            console.warn('[HIBP] Gagal memeriksa kebocoran:', e);
            return 0;
        }
    }

    async function checkAllPasswordsAgainstHibp() {
        const btn = document.getElementById('btn-check-hibp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> <span>Memeriksa Dark Web (k-Anonymity)...</span>`;

        let breachedCount = 0;
        try {
            for (const a of auditItemsData) {
                const count = await checkPwnedPassword(a.item.data.password);
                a.breachCount = count;
                if (count > 0) breachedCount++;
            }

            // Juga periksa item yang sebelumnya tidak weak/reused
            const allLoginItems = VaultApp.getDecryptedItems().filter(i => i.item_type === 'login' && i.data.password);
            for (const item of allLoginItems) {
                let existing = auditItemsData.find(a => a.item.id === item.id);
                if (!existing) {
                    const count = await checkPwnedPassword(item.data.password);
                    if (count > 0) {
                        breachedCount++;
                        auditItemsData.push({
                            item: item,
                            isWeak: false,
                            isReused: false,
                            reusedTimes: 1,
                            breachCount: count,
                            strength: PasswordGenerator.evaluateStrength(item.data.password)
                        });
                    }
                }
            }

            document.getElementById('audit-breached-pwd').textContent = breachedCount;
            if (breachedCount > 0) {
                AppToast.show(`PERINGATAN: Ditemukan ${breachedCount} kata sandi yang bocor di database internet!`, 'error');
            } else {
                AppToast.show('Kabar baik! Tidak ada kata sandi Anda yang terdeteksi bocor di internet.', 'success');
            }

            renderAuditItems();
        } catch (err) {
            AppToast.show('Gagal memeriksa basis data kebocoran: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }

    async function runSecurityAudit() {
        const items = VaultApp.getDecryptedItems().filter(i => i.item_type === 'login' && i.data.password);
        document.getElementById('audit-total-pwd').textContent = items.length;

        const pwdFrequency = {};
        items.forEach(i => {
            const p = i.data.password;
            pwdFrequency[p] = (pwdFrequency[p] || 0) + 1;
        });

        auditItemsData = [];
        let weakCount = 0;
        let reusedCount = 0;
        let agedCount = 0;
        const now = Date.now();

        items.forEach(i => {
            const p = i.data.password;
            const strength = PasswordGenerator.evaluateStrength(p);
            const isWeak = strength.score < 3 || p.length < 12;
            const isReused = pwdFrequency[p] > 1;

            let diffDays = 0;
            if (i.updated_at) {
                const updatedDate = new Date(i.updated_at.replace(' ', 'T'));
                if (!isNaN(updatedDate.getTime())) {
                    diffDays = Math.floor((now - updatedDate.getTime()) / (1000 * 60 * 60 * 24));
                }
            }
            const isAged = diffDays >= 180; // Lebih dari 6 bulan

            if (isWeak) weakCount++;
            if (isReused) reusedCount++;
            if (isAged) agedCount++;

            if (isWeak || isReused || isAged) {
                auditItemsData.push({
                    item: i,
                    isWeak: isWeak,
                    isReused: isReused,
                    isAged: isAged,
                    ageDays: diffDays,
                    reusedTimes: pwdFrequency[p],
                    breachCount: breachedCache[p] || 0,
                    strength: strength
                });
            }
        });

        document.getElementById('audit-weak-pwd').textContent = weakCount;
        document.getElementById('audit-reused-pwd').textContent = reusedCount;
        document.getElementById('audit-aged-pwd').textContent = agedCount;

        renderAuditItems();
    }

    function renderAuditItems() {
        const container = document.getElementById('audit-list-container');
        let filtered = auditItemsData;

        if (currentAuditFilter === 'weak') {
            filtered = filtered.filter(a => a.isWeak);
        } else if (currentAuditFilter === 'reused') {
            filtered = filtered.filter(a => a.isReused);
        } else if (currentAuditFilter === 'breached') {
            filtered = filtered.filter(a => (a.breachCount || 0) > 0);
        } else if (currentAuditFilter === 'aged') {
            filtered = filtered.filter(a => a.isAged);
        }

        if (filtered.length === 0) {
            container.innerHTML = `
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak Ditemukan Masalah Keamanan</h4>
                <p class="text-xs text-gray-400 mt-1">Kategori ini tidak memiliki kata sandi bermasalah.</p>
            </div>`;
            return;
        }

        container.innerHTML = filtered.map(a => {
            const it = a.item;
            const d = it.data;

            const tags = [];
            if ((a.breachCount || 0) > 0) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-rose-600 text-white animate-pulse">Bocor ${a.breachCount.toLocaleString()}x di Dark Web</span>`);
            }
            if (a.isWeak) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">Sandi Lemah (${d.password.length} char)</span>`);
            }
            if (a.isReused) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">Digunakan ${a.reusedTimes}x Akun</span>`);
            const explanations = [];
            if ((a.breachCount || 0) > 0) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-rose-600 text-white animate-pulse">Bocor ${a.breachCount.toLocaleString()}x di Dark Web</span>`);
                explanations.push(`
                    <div class="flex items-start space-x-2 text-rose-700 dark:text-rose-400">
                        <span class="font-bold text-sm leading-none mt-0.5">•</span>
                        <span><strong>Bocor di Internet & Dark Web (${a.breachCount.toLocaleString()}x):</strong> Kata sandi ini terdaftar dalam database kebocoran HaveIBeenPwned akibat insiden pembobolan data di berbagai layanan online dunia. Peretas menggunakan sandi ini dalam kamus tebakan otomatis (*credential stuffing*).</span>
                    </div>
                `);
            }
            if (a.isWeak) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">Sandi Lemah (${d.password.length} Char)</span>`);
                explanations.push(`
                    <div class="flex items-start space-x-2 text-rose-600 dark:text-rose-300">
                        <span class="font-bold text-sm leading-none mt-0.5">•</span>
                        <span><strong>Kekuatan Sandi Lemah:</strong> Panjang kata sandi hanya <strong>${d.password.length} karakter</strong> (standar aman minimal 14–16 karakter). Sandi pendek sangat rentan ditembus metode brute-force dalam hitungan menit.</span>
                    </div>
                `);
            }
            if (a.isReused) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">Digunakan ${a.reusedTimes}x Akun</span>`);
                explanations.push(`
                    <div class="flex items-start space-x-2 text-amber-600 dark:text-amber-300">
                        <span class="font-bold text-sm leading-none mt-0.5">•</span>
                        <span><strong>Penggunaan Ulang Sandi:</strong> Kata sandi ini dipakai bersamaan di <strong>${a.reusedTimes} akun berbeda</strong>. Jika salah satu layanan tersebut bocor, akun lain ikut terancam.</span>
                    </div>
                `);
            }
            if (a.isAged) {
                tags.push(`<span class="px-2 py-0.5 text-[10px] uppercase font-bold rounded bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-300">Usang (${a.ageDays} hari lalu)</span>`);
                explanations.push(`
                    <div class="flex items-start space-x-2 text-orange-600 dark:text-orange-300">
                        <span class="font-bold text-sm leading-none mt-0.5">•</span>
                        <span><strong>Sandi Usang:</strong> Terakhir diperbarui <strong>${a.ageDays} hari lalu</strong> (&gt; 180 hari). Disarankan untuk mengganti kata sandi secara berkala.</span>
                    </div>
                `);
            }

            return `
            <div class="p-4.5 hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">${VaultApp.escapeHtml ? VaultApp.escapeHtml(d.name) : d.name}</h4>
                            ${tags.join(' ')}
                        </div>
                        <p class="text-xs text-gray-400 font-mono flex items-center space-x-2">
                            <span>User: <strong class="text-gray-700 dark:text-gray-300 font-semibold">${VaultApp.escapeHtml ? VaultApp.escapeHtml(d.username || 'Tanpa Username') : (d.username || 'Tanpa Username')}</strong></span>
                            <span>•</span>
                            <span class="truncate">${d.url ? (VaultApp.escapeHtml ? VaultApp.escapeHtml(d.url) : d.url) : 'Tanpa URL'}</span>
                        </p>

                        <!-- Tombol Expand Penjelasan Khusus -->
                        <button type="button" onclick="toggleAuditDetail(${it.id})" class="mt-2.5 inline-flex items-center space-x-1.5 text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition focus:outline-hidden">
                            <span>🔍 Penjelasan Rinci & Sumber Temuan</span>
                            <svg id="audit-icon-${it.id}" class="w-3.5 h-3.5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <div class="flex items-center space-x-2 flex-shrink-0 mt-0.5">
                        <button type="button" onclick="openEditItemModal(VaultApp.getDecryptedItems().find(i => i.id == ${it.id}))" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Perbarui Sandi</span>
                        </button>
                    </div>
                </div>

                <!-- Kontainer Penjelasan Rinci (Collapsible) -->
                <div id="audit-detail-${it.id}" class="hidden mt-3 p-4 bg-gray-50/90 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 rounded-xl space-y-2.5 text-xs">
                    <div class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-1.5 flex items-center justify-between">
                        <span>Hasil Diagnosa Keamanan:</span>
                        <span class="text-[11px] font-normal text-gray-400">Target Username: <strong>${VaultApp.escapeHtml ? VaultApp.escapeHtml(d.username || 'User') : (d.username || 'User')}</strong></span>
                    </div>
                    <div class="space-y-1.5 leading-relaxed">
                        ${explanations.join('')}
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700/80 text-[11px] text-gray-500 dark:text-gray-400">
                        💡 <strong>Solusi Aman:</strong> Klik tombol <em>Perbarui Sandi</em> untuk membuat kata sandi acak baru minimal 16 karakter via generator.
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function toggleAuditDetail(id) {
        const el = document.getElementById(`audit-detail-${id}`);
        const icon = document.getElementById(`audit-icon-${id}`);
        if (el) {
            el.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }
    }

    function filterAuditList(type) {
        currentAuditFilter = type;
        ['all', 'weak', 'reused', 'breached', 'aged'].forEach(t => {
            const btn = document.getElementById(`audit-filter-${t}`);
            if (!btn) return;
            if (t === type) {
                btn.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 transition';
            } else {
                btn.className = 'px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition';
            }
        });
        renderAuditItems();
    }

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            runSecurityAudit();
        }, 800);
    });
</script>
