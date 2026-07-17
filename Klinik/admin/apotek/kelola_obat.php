<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Kelola Obat Apotek';
$active = 'apotek';

$id_apotek = isset($_GET['id_apotek']) ? (int)$_GET['id_apotek'] : 0;
if ($id_apotek <= 0) {
    set_flash('error', 'Apotek tidak valid.');
    redirect(BASE_URL . 'admin/apotek/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        set_flash('error', 'Token CSRF tidak valid.');
        redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
    }

    $act = $_POST['act'] ?? '';
    $id_obat = isset($_POST['id_obat']) ? (int)$_POST['id_obat'] : 0;
    if ($id_obat <= 0) {
        set_flash('error', 'Obat tidak valid.');
        redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
    }

    if ($act === 'hapus') {
        $del = $pdo->prepare("DELETE FROM harga_stok_apotek WHERE id_apotek=? AND id_obat=?");
        $del->execute([$id_apotek, $id_obat]);
        set_flash('success', 'Data stok/harga apotek berhasil dihapus.');
        redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
    }

    $harga_apotek = $_POST['harga_apotek'] ?? '';
    $stok_apotek = $_POST['stok_apotek'] ?? '';

    if (!is_numeric($harga_apotek) || (float)$harga_apotek <= 0) {
        set_flash('error', 'Harga harus berupa angka lebih besar dari 0.');
        redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
    }
    if (!is_numeric($stok_apotek) || (int)$stok_apotek < 0) {
        set_flash('error', 'Stok harus berupa angka dan tidak boleh negatif.');
        redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
    }

    $harga_apotek = (float)$harga_apotek;
    $stok_apotek = (int)$stok_apotek;

    if ($act === 'tambah') {
        $ins = $pdo->prepare("INSERT INTO harga_stok_apotek (id_apotek, id_obat, harga, stok) VALUES (?, ?, ?, ?)");
        $ins->execute([$id_apotek, $id_obat, $harga_apotek, $stok_apotek]);
        set_flash('success', 'Data stok/harga apotek berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $upd = $pdo->prepare("UPDATE harga_stok_apotek SET harga=?, stok=? WHERE id_apotek=? AND id_obat=?");
        $upd->execute([$harga_apotek, $stok_apotek, $id_apotek, $id_obat]);
        set_flash('success', 'Data stok/harga apotek berhasil diperbarui.');
    }

    redirect(BASE_URL . 'admin/apotek/kelola_obat.php?id_apotek=' . $id_apotek);
}

$apotek = $pdo->prepare("SELECT a.*, kt.nama_kota FROM apotek a LEFT JOIN kota kt ON kt.id_kota=a.id_kota WHERE a.id_apotek=?");
$apotek->execute([$id_apotek]);
$apotek = $apotek->fetch();
if (!$apotek) {
    set_flash('error', 'Apotek tidak ditemukan.');
    redirect(BASE_URL . 'admin/apotek/');
}

$obats = $pdo->prepare("SELECT o.*, k.nama_kategori, s.nama_supplier, hsa.harga AS apotek_harga, hsa.stok AS apotek_stok FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier LEFT JOIN harga_stok_apotek hsa ON hsa.id_obat=o.id_obat AND hsa.id_apotek=? ORDER BY o.nama_obat");
$obats->execute([$id_apotek]);
$obats = $obats->fetchAll();

function img_obat_base($g) {
    return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg';
}

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span><i class="fas fa-store me-2"></i>Kelola Obat untuk Apotek <?= e($apotek['nama_apotek']) ?></span>
            <div class="text-muted small">Edit harga dan stok khusus untuk apotek ini.</div>
        </div>
        <a href="<?= BASE_URL ?>admin/apotek/" class="btn btn-sm btn-secondary">Kembali ke Daftar Apotek</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="table" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Harga Apotek</th>
                        <th>Stok Apotek</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($obats as $o): ?>
                        <tr>
                            <td><img src="<?= img_obat_base($o['gambar']) ?>" class="img-thumb"></td>
                            <td><?= e($o['kode_obat']) ?></td>
                            <td><?= e($o['nama_obat']) ?></td>
                            <td><?= e($o['nama_kategori']) ?></td>
                            <td><?= e($o['nama_supplier']) ?></td>
                            <td><?= $o['apotek_harga'] !== null ? rupiah($o['apotek_harga']) : '<span class="text-muted">-</span>' ?></td>
                            <td><?= $o['apotek_stok'] !== null ? e($o['apotek_stok']) : '<span class="text-muted">-</span>' ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-<?= $o['apotek_harga'] !== null ? 'warning' : 'success' ?>" data-bs-toggle="modal" data-bs-target="#modalForm"
                                    data-act="<?= $o['apotek_harga'] !== null ? 'edit' : 'tambah' ?>"
                                    data-id_obat="<?= $o['id_obat'] ?>"
                                    data-nama="<?= e($o['nama_obat']) ?>"
                                    data-harga="<?= $o['apotek_harga'] !== null ? e($o['apotek_harga']) : '' ?>"
                                    data-stok="<?= $o['apotek_stok'] !== null ? e($o['apotek_stok']) : '' ?>">
                                    <?= $o['apotek_harga'] !== null ? 'Edit' : 'Tambah' ?>
                                </button>
                                <?php if ($o['apotek_harga'] !== null): ?>
                                    <form method="post" class="d-inline-block ms-1" onsubmit="return confirm('Hapus data stok/harga untuk obat ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="act" value="hapus">
                                        <input type="hidden" name="id_obat" value="<?= $o['id_obat'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" id="formModal">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="modalAct" value="tambah">
            <input type="hidden" name="id_obat" id="modalIdObat" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Harga & Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong>Obat:</strong> <span id="modalObatNama"></span></p>
                <input type="hidden" name="id_apotek" value="<?= $id_apotek ?>">
                <div class="mb-3">
                    <label class="form-label">Harga Apotek</label>
                    <input type="number" name="harga_apotek" id="modalHarga" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok Apotek</label>
                    <input type="number" name="stok_apotek" id="modalStok" class="form-control" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="modalSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
var modalForm = document.getElementById('modalForm');
modalForm.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var act = button.getAttribute('data-act');
    var idObat = button.getAttribute('data-id_obat');
    var nama = button.getAttribute('data-nama');
    var harga = button.getAttribute('data-harga');
    var stok = button.getAttribute('data-stok');

    document.getElementById('modalAct').value = act;
    document.getElementById('modalIdObat').value = idObat;
    document.getElementById('modalObatNama').textContent = nama;
    document.getElementById('modalTitle').textContent = act === 'edit' ? 'Edit Harga & Stok' : 'Tambah Harga & Stok';
    document.getElementById('modalHarga').value = harga;
    document.getElementById('modalStok').value = stok;
});
</script>
<?php require_once '../../includes/footer_admin.php';
