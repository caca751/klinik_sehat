/* ============================================================
   assets/js/main.js
   Fungsi global: dark mode, toast, konfirmasi, datatables,
   realtime search, ajax keranjang, review, notifikasi
   ============================================================ */
(function () {
    'use strict';

    const BASE = (window.APP_BASE_URL || '');

    /* ---------- DARK MODE ---------- */
    function applyDark(mode) {
        if (mode === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        const t = document.getElementById('darkToggle');
        if (t) {
            t.innerHTML = mode === 'dark'
                ? '<i class="fas fa-sun"></i> <span class="mode-label">Terang</span>'
                : '<i class="fas fa-moon"></i> <span class="mode-label">Gelap</span>';
            t.title = mode === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
        }
    }
    const saved = localStorage.getItem('theme') || window.APP_DEFAULT_THEME || 'light';
    applyDark(saved);
    document.addEventListener('click', function (e) {
        if (e.target.closest('#darkToggle')) {
            const dark = document.body.classList.toggle('dark-mode');
            const mode = dark ? 'dark' : 'light';
            applyDark(mode);
            localStorage.setItem('theme', mode);
            fetch(BASE + 'api/theme.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mode: mode }) });
        }
    });

    /* ---------- SIDEBAR MOBILE ---------- */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.topbar .toggle')) {
            document.querySelector('.sidebar')?.classList.toggle('open');
        }
        if (e.target.classList.contains('sidebar')) {
            document.querySelector('.sidebar')?.classList.remove('open');
        }
    });

    /* ---------- TOAST ---------- */
    window.toast = function (msg, type) {
        type = type || 'success';
        const colors = { success: '#16a34a', error: '#dc2626', info: '#0ea5e9', warning: '#f59e0b' };
        const el = document.createElement('div');
        el.style.cssText = 'position:fixed;top:20px;right:20px;background:' + (colors[type] || '#16a34a') +
            ';color:#fff;padding:12px 18px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:9999;font-size:14px;max-width:320px;';
        el.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'times-circle' : type === 'info' ? 'info-circle' : 'check-circle') + '"></i> ' + msg;
        document.body.appendChild(el);
        setTimeout(function () { el.style.opacity = '0'; el.style.transition = 'opacity .4s'; setTimeout(() => el.remove(), 400); }, 3000);
    };

    /* ---------- KONFIRMASI HAPUS ---------- */
    window.konfirmHapus = function (url, pesan) {
        Swal.fire({
            title: 'Yakin?', text: pesan || 'Data akan dihapus permanen.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
        }).then((r) => { if (r.isConfirmed) window.location.href = url; });
    };

    /* ---------- AJAX HELPER ---------- */
    window.postData = function (url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        }).then(r => r.json());
    };

    /* ---------- REALTIME SEARCH (produk) ---------- */
    const searchInput = document.getElementById('liveSearch');
    const filterKategori = document.getElementById('filterKategori');
    const filterHargaMin = document.getElementById('filterHargaMin');
    const filterHargaMax = document.getElementById('filterHargaMax');
    const filterApotek = document.getElementById('filterApotek');
    if (searchInput) {
        let timer;
        function doSearch(){
            clearTimeout(timer);
            const q = searchInput.value;
            const kat = filterKategori ? filterKategori.value : '';
            const hmin = filterHargaMin ? filterHargaMin.value : '';
            const hmax = filterHargaMax ? filterHargaMax.value : '';
            const apt = filterApotek ? filterApotek.value : '';
            timer = setTimeout(function () {
                const box = document.getElementById('produkGrid');
                box.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Mencari...</p></div>';
                let url = BASE + 'api/obat_search.php?q=' + encodeURIComponent(q) + '&kategori=' + encodeURIComponent(kat) + '&harga_min=' + encodeURIComponent(hmin) + '&harga_max=' + encodeURIComponent(hmax);
                if (apt) url += '&id_apotek=' + encodeURIComponent(apt);
                fetch(url)
                    .then(r => r.json()).then(res => {
                        if (res.status === 'ok') {
                            box.innerHTML = res.html || '<div class="empty-state"><i class="fas fa-box-open"></i><p>Obat tidak ditemukan.</p></div>';
                        }
                    });
            }, 300);
        }
        searchInput.addEventListener('input', doSearch);
        if (filterKategori) filterKategori.addEventListener('change', doSearch);
        if (filterHargaMin) filterHargaMin.addEventListener('input', doSearch);
        if (filterHargaMax) filterHargaMax.addEventListener('input', doSearch);
        if (filterApotek) filterApotek.addEventListener('change', doSearch);
    }

    /* ---------- KERANJANG AJAX ---------- */
    window.addToCart = function (id_obat, id_apotek) {
        var data = { act: 'add', id_obat: id_obat, csrf_token: window.CSRF };
        if (id_apotek) data.id_apotek = id_apotek;
        postData(BASE + 'api/keranjang.php', data)
            .then(res => {
                if (res.status === 'ok') { toast(res.msg); updateCartBadge(res.count); }
                else { toast(res.msg, 'error'); }
            });
    };
    window.changeQty = function (id_keranjang, delta, input) {
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
        postData(BASE + 'api/keranjang.php', { act: 'update', id_keranjang: id_keranjang, jumlah: val, csrf_token: window.CSRF })
            .then(res => { if (res.status === 'ok') location.reload(); else toast(res.msg, 'error'); });
    };
    window.removeCart = function (id_keranjang) {
        postData(BASE + 'api/keranjang.php', { act: 'remove', id_keranjang: id_keranjang, csrf_token: window.CSRF })
            .then(res => { if (res.status === 'ok') location.reload(); else toast(res.msg, 'error'); });
    };
    function updateCartBadge(c) {
        const b = document.getElementById('cartBadge');
        if (b) { b.textContent = c; b.style.display = c > 0 ? 'inline-block' : 'none'; }
    }

    /* ---------- NOTIFIKASI ---------- */
    window.markNotifRead = function (id) {
        postData(BASE + 'api/notifikasi.php', { act: 'read', id: id, csrf_token: window.CSRF });
    };
    window.markAllRead = function () {
        postData(BASE + 'api/notifikasi.php', { act: 'read_all', csrf_token: window.CSRF }).then(() => location.reload());
    };

    window.APP_BASE_URL = BASE;
})();
