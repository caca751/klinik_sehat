<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Klinik';
$active = 'klinik';
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $id_kota = (int)$_POST['id_kota'];
    $nama = clean($_POST['nama_klinik']);
    $alamat = clean($_POST['alamat']);
    $telp = clean($_POST['no_telp']);
    $email = clean($_POST['email']);
    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO klinik (id_kota,nama_klinik,alamat,no_telp,email) VALUES (?,?,?,?,?)")->execute([$id_kota, $nama, $alamat, $telp, $email]);
        set_flash('success', 'Klinik berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE klinik SET id_kota=?,nama_klinik=?,alamat=?,no_telp=?,email=? WHERE id_klinik=?")->execute([$id_kota, $nama, $alamat, $telp, $email, $id]);
        set_flash('success', 'Klinik berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM klinik WHERE id_klinik=?")->execute([$id]);
    set_flash('success', 'Klinik berhasil dihapus.');
    redirect('');
}

$list = $pdo->query("SELECT k.*, kt.nama_kota FROM klinik k LEFT JOIN kota kt ON kt.id_kota=k.id_kota ORDER BY k.nama_klinik")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-hospital me-2"></i>Daftar Klinik</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama Klinik</th><th>Kota</th><th>Alamat</th><th>Telp</th><th>Email</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_klinik']) ?></td>
                    <td><?= e($r['nama_klinik']) ?></td>
                    <td><?= e($r['nama_kota']) ?></td>
                    <td><?= e($r['alamat']) ?></td>
                    <td><?= e($r['no_telp']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($r['id_klinik']) ?>" data-kota="<?= e($r['id_kota']) ?>" data-nama="<?= e($r['nama_klinik']) ?>" data-alamat="<?= e($r['alamat']) ?>" data-telp="<?= e($r['no_telp']) ?>" data-email="<?= e($r['email']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_klinik']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus klinik ini?')"><i class="fas fa-trash"></i></a>
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
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Klinik</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Kota</label><select name="id_kota" id="id_kota" class="form-select" required><?php foreach($kotas as $kt): ?><option value="<?= e($kt['id_kota']) ?>"><?= e($kt['nama_kota']) ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label">Nama Klinik</label><input type="text" name="nama_klinik" id="nama_klinik" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="alamat" class="form-control" rows="2"></textarea></div>
            <div class="mb-3"><label class="form-label">No Telp</label><input type="text" name="no_telp" id="no_telp" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="email" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget,id=btn.getAttribute('data-id');
    if(id){document.getElementById('act').value='edit';document.getElementById('id').value=id;document.getElementById('id_kota').value=btn.getAttribute('data-kota');document.getElementById('nama_klinik').value=btn.getAttribute('data-nama');document.getElementById('alamat').value=btn.getAttribute('data-alamat');document.getElementById('no_telp').value=btn.getAttribute('data-telp');document.getElementById('email').value=btn.getAttribute('data-email');document.getElementById('modalTitle').textContent='Edit Klinik';}
    else{document.getElementById('act').value='tambah';document.getElementById('id').value='';document.getElementById('nama_klinik').value='';document.getElementById('alamat').value='';document.getElementById('no_telp').value='';document.getElementById('email').value='';document.getElementById('modalTitle').textContent='Tambah Klinik';}
});
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
