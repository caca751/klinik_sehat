<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Apotek';
$active = 'apotek';
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();
$selected_apotek = isset($_GET['id_apotek']) ? (int)$_GET['id_apotek'] : 0;
$selected_apotek_info = null;
if ($selected_apotek > 0) {
    $selected_apotek_info = $pdo->prepare("SELECT a.*, kt.nama_kota FROM apotek a LEFT JOIN kota kt ON kt.id_kota=a.id_kota WHERE a.id_apotek=?");
    $selected_apotek_info->execute([$selected_apotek]);
    $selected_apotek_info = $selected_apotek_info->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    if ($act === 'tambah' || $act === 'edit') {
        $id_kota = (int)$_POST['id_kota'];
        $nama = clean($_POST['nama_apotek']);
        $alamat = clean($_POST['alamat']);
        $telp = clean($_POST['no_telp']);
        $email = clean($_POST['email']);
        if ($act === 'tambah') {
            $pdo->prepare("INSERT INTO apotek (id_kota,nama_apotek,alamat,no_telp,email) VALUES (?,?,?,?,?)")->execute([$id_kota, $nama, $alamat, $telp, $email]);
            set_flash('success', 'Apotek berhasil ditambahkan.');
        } else {
            $id = (int)$_POST['id'];
            $pdo->prepare("UPDATE apotek SET id_kota=?,nama_apotek=?,alamat=?,no_telp=?,email=? WHERE id_apotek=?")->execute([$id_kota, $nama, $alamat, $telp, $email, $id]);
            set_flash('success', 'Apotek berhasil diupdate.');
        }
        redirect('');
    }
    if ($act === 'simpan_stok') {
        $id_apotek = (int)$_POST['id_apotek'];
        $id_obat = (int)$_POST['id_obat'];
        $harga_apotek = (float)$_POST['harga_apotek'];
        $stok_apotek = (int)$_POST['stok_apotek'];
        if ($id_apotek && $id_obat) {
            $upd = $pdo->prepare("UPDATE harga_stok_apotek SET harga=?, stok=? WHERE id_apotek=? AND id_obat=?");
            $upd->execute([$harga_apotek, $stok_apotek, $id_apotek, $id_obat]);
            if ($upd->rowCount() === 0) {
                $pdo->prepare("INSERT INTO harga_stok_apotek (id_apotek,id_obat,harga,stok) VALUES (?,?,?,?)")->execute([$id_apotek, $id_obat, $harga_apotek, $stok_apotek]);
            }
            set_flash('success', 'Stok dan harga apotek berhasil disimpan.');
        } else {
            set_flash('error', 'Data apotek atau obat tidak valid.');
        }
        redirect('?id_apotek=' . $id_apotek);
    }
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM apotek WHERE id_apotek=?")->execute([$id]);
    set_flash('success', 'Apotek berhasil dihapus.');
    redirect('');
}
if (isset($_GET['hapus_stok'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id_apotek = isset($_GET['id_apotek']) ? (int)$_GET['id_apotek'] : 0;
    $id_obat = (int)$_GET['hapus_stok'];
    if ($id_apotek && $id_obat) {
        $pdo->prepare("DELETE FROM harga_stok_apotek WHERE id_apotek=? AND id_obat=?")->execute([$id_apotek, $id_obat]);
        set_flash('success', 'Data stok apotek berhasil dihapus.');
    }
    redirect('?id_apotek=' . $id_apotek);
}

if ($selected_apotek > 0) {
    $obats = $pdo->prepare("SELECT o.*, k.nama_kategori, s.nama_supplier, hsa.harga AS apotek_harga, hsa.stok AS apotek_stok FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier LEFT JOIN harga_stok_apotek hsa ON hsa.id_obat=o.id_obat AND hsa.id_apotek=? ORDER BY o.nama_obat");
    $obats->execute([$selected_apotek]);
    $obats = $obats->fetchAll();
} else {
    $obats = $pdo->query("SELECT o.*, k.nama_kategori, s.nama_supplier FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier ORDER BY o.id_obat")->fetchAll();
}

$list = $pdo->query("SELECT a.*, kt.nama_kota FROM apotek a LEFT JOIN kota kt ON kt.id_kota=a.id_kota ORDER BY a.id_apotek")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-building me-2"></i>Daftar Apotek</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama Apotek</th><th>Kota</th><th>Alamat</th><th>Telp</th><th>Email</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_apotek']) ?></td>
                    <td><?= e($r['nama_apotek']) ?></td>
                    <td><?= e($r['nama_kota']) ?></td>
                    <td><?= e($r['alamat']) ?></td>
                    <td><?= e($r['no_telp']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>admin/apotek/kelola_obat.php?id_apotek=<?= e($r['id_apotek']) ?>" class="btn btn-sm btn-secondary me-1" title="Kelola Obat"><i class="fas fa-store"></i></a>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($r['id_apotek']) ?>" data-kota="<?= e($r['id_kota']) ?>" data-nama="<?= e($r['nama_apotek']) ?>" data-alamat="<?= e($r['alamat']) ?>" data-telp="<?= e($r['no_telp']) ?>" data-email="<?= e($r['email']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_apotek']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus apotek ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($selected_apotek_info): ?>
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span><i class="fas fa-store me-2"></i>Kelola Stok & Harga Obat untuk <?= e($selected_apotek_info['nama_apotek']) ?></span>
            <div class="text-muted small">Edit harga dan stok obat khusus untuk apotek ini. Setiap apotek dapat memiliki data harga/stok berbeda.</div>
        </div>
        <a href="<?= BASE_URL ?>admin/apotek/" class="btn btn-sm btn-secondary">Kembali ke Daftar Apotek</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" style="width:100%">
                <thead><tr><th>Gambar</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Supplier</th><th>Harga Apotek</th><th>Stok Apotek</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($obats as $o): ?>
                    <tr>
                        <td><img src="<?= img_obat($o['gambar']) ?>" class="img-thumb"></td>
                        <td><?= e($o['kode_obat']) ?></td>
                        <td><?= e($o['nama_obat']) ?></td>
                        <td><?= e($o['nama_kategori']) ?></td>
                        <td><?= e($o['nama_supplier']) ?></td>
                        <td><input type="number" class="form-control form-control-sm harga-apotek" min="0" step="0.01" value="<?= $o['apotek_harga'] !== null ? e($o['apotek_harga']) : '' ?>" placeholder="Harga"></td>
                        <td><input type="number" class="form-control form-control-sm stok-apotek" min="0" value="<?= $o['apotek_stok'] !== null ? e($o['apotek_stok']) : '' ?>" placeholder="Stok"></td>
                        <td class="text-end">
                            <form method="post" class="d-inline-block stok-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="act" value="simpan_stok">
                                <input type="hidden" name="id_apotek" value="<?= $selected_apotek ?>">
                                <input type="hidden" name="id_obat" value="<?= $o['id_obat'] ?>">
                                <input type="hidden" name="harga_apotek" class="harga-hidden">
                                <input type="hidden" name="stok_apotek" class="stok-hidden">
                                <button type="button" class="btn btn-sm btn-primary" onclick="submitStokRow(this)">Simpan</button>
                                <?php if ($o['apotek_harga'] !== null || $o['apotek_stok'] !== null): ?>
                                    <a href="?id_apotek=<?= $selected_apotek ?>&hapus_stok=<?= $o['id_obat'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data stok/harga untuk obat ini?')">Hapus</a>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" id="formModal">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="modalAct" value="tambah">
            <input type="hidden" name="id" id="modalId">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Apotek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kota</label>
                    <select name="id_kota" class="form-select" required id="modalKota">
                        <option value="">Pilih Kota</option>
                        <?php foreach($kotas as $kt): ?><option value="<?= $kt['id_kota'] ?>"><?= e($kt['nama_kota']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Apotek</label>
                    <input type="text" name="nama_apotek" class="form-control" required id="modalNama">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2" required id="modalAlamat"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Telp</label>
                    <input type="text" name="no_telp" class="form-control" required id="modalTelp">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required id="modalEmail">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
var modalForm = document.getElementById('modalForm');
modalForm.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    var id = btn.getAttribute('data-id');
    document.getElementById('modalAct').value = id ? 'edit' : 'tambah';
    document.getElementById('modalTitle').textContent = id ? 'Edit Apotek' : 'Tambah Apotek';
    document.getElementById('modalId').value = id || '';
    document.getElementById('modalKota').value = btn.getAttribute('data-kota') || '';
    document.getElementById('modalNama').value = btn.getAttribute('data-nama') || '';
    document.getElementById('modalAlamat').value = btn.getAttribute('data-alamat') || '';
    document.getElementById('modalTelp').value = btn.getAttribute('data-telp') || '';
    document.getElementById('modalEmail').value = btn.getAttribute('data-email') || '';
});
modalForm.addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalAct').value = 'tambah';
    document.getElementById('modalId').value = '';
    document.getElementById('modalKota').value = '';
    document.getElementById('modalNama').value = '';
    document.getElementById('modalAlamat').value = '';
    document.getElementById('modalTelp').value = '';
    document.getElementById('modalEmail').value = '';
});
function submitStokRow(btn) {
    var row = btn.closest('tr');
    var form = btn.closest('form');
    var harga = row.querySelector('.harga-apotek').value;
    var stok = row.querySelector('.stok-apotek').value;
    form.querySelector('.harga-hidden').value = harga;
    form.querySelector('.stok-hidden').value = stok;
    form.submit();
}
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
