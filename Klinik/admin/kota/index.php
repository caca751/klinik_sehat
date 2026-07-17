<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Kota';
$active = 'kota';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_kota']);
    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO kota (nama_kota) VALUES (?)")->execute([$nama]);
        set_flash('success', 'Kota berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE kota SET nama_kota=? WHERE id_kota=?")->execute([$nama, $id]);
        set_flash('success', 'Kota berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM kota WHERE id_kota=?")->execute([$id]);
    set_flash('success', 'Kota berhasil dihapus.');
    redirect('');
}

$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-map-marker-alt me-2"></i>Daftar Kota</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama Kota</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($kotas as $k): ?>
                <tr>
                    <td><?= e($k['id_kota']) ?></td>
                    <td><?= e($k['nama_kota']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($k['id_kota']) ?>" data-nama="<?= e($k['nama_kota']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($k['id_kota']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kota ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modalForm" tabindex="-1"><div class="modal-dialog">
    <form method="post" class="modal-content">
        <?= csrf_field() ?>
        <input type="hidden" name="act" id="act" value="tambah">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Kota</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Nama Kota</label><input type="text" name="nama_kota" id="nama_kota" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget,id=btn.getAttribute('data-id');
    if(id){document.getElementById('act').value='edit';document.getElementById('id').value=id;document.getElementById('nama_kota').value=btn.getAttribute('data-nama');document.getElementById('modalTitle').textContent='Edit Kota';}
    else{document.getElementById('act').value='tambah';document.getElementById('id').value='';document.getElementById('nama_kota').value='';document.getElementById('modalTitle').textContent='Tambah Kota';}
});
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
