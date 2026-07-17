<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Spesialis';
$active = 'spesialis';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_spesialis']);
    $desc = clean($_POST['deskripsi']);
    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO spesialis (nama_spesialis, deskripsi) VALUES (?,?)")->execute([$nama, $desc]);
        set_flash('success', 'Spesialis berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE spesialis SET nama_spesialis=?, deskripsi=? WHERE id_spesialis=?")->execute([$nama, $desc, $id]);
        set_flash('success', 'Spesialis berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM spesialis WHERE id_spesialis=?")->execute([$id]);
    set_flash('success', 'Spesialis berhasil dihapus.');
    redirect('');
}

$list = $pdo->query("SELECT * FROM spesialis ORDER BY nama_spesialis")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-stethoscope me-2"></i>Daftar Spesialis</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama Spesialis</th><th>Deskripsi</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_spesialis']) ?></td>
                    <td><?= e($r['nama_spesialis']) ?></td>
                    <td><?= e($r['deskripsi']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($r['id_spesialis']) ?>" data-nama="<?= e($r['nama_spesialis']) ?>" data-deskripsi="<?= e($r['deskripsi']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_spesialis']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus spesialis ini?')"><i class="fas fa-trash"></i></a>
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
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Spesialis</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Nama Spesialis</label><input type="text" name="nama_spesialis" id="nama_spesialis" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget,id=btn.getAttribute('data-id');
    if(id){document.getElementById('act').value='edit';document.getElementById('id').value=id;document.getElementById('nama_spesialis').value=btn.getAttribute('data-nama');document.getElementById('deskripsi').value=btn.getAttribute('data-deskripsi');document.getElementById('modalTitle').textContent='Edit Spesialis';}
    else{document.getElementById('act').value='tambah';document.getElementById('id').value='';document.getElementById('nama_spesialis').value='';document.getElementById('deskripsi').value='';document.getElementById('modalTitle').textContent='Tambah Spesialis';}
});
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
