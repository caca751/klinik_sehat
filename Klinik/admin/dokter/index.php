<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Dokter';
$active = 'dokter';
$spesialis_list = $pdo->query("SELECT * FROM spesialis ORDER BY nama_spesialis")->fetchAll();
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $id_spesialis = (int)$_POST['id_spesialis'];
    $nama = clean($_POST['nama_dokter']);
    $gender = clean($_POST['gender']);
    $hp = clean($_POST['no_hp']);
    $email = clean($_POST['email']);
    $str = clean($_POST['str_no']);
    $biaya = (float)str_replace(['.',','],'',$_POST['biaya_konsultasi']);
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $up = upload_file($_FILES['foto'], APP_ROOT . '/uploads/dokter/');
        if ($up['success']) $foto = $up['file'];
    }
    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO dokter (id_spesialis,nama_dokter,gender,no_hp,email,str_no,biaya_konsultasi,foto) VALUES (?,?,?,?,?,?,?,?)")->execute([$id_spesialis,$nama,$gender,$hp,$email,$str,$biaya,$foto]);
        set_flash('success', 'Dokter berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        if ($foto) {
            $stmt = $pdo->prepare("SELECT foto FROM dokter WHERE id_dokter=?");
            $stmt->execute([$id]); $row = $stmt->fetch();
            if ($row['foto'] && file_exists(APP_ROOT . '/uploads/dokter/' . $row['foto'])) unlink(APP_ROOT . '/uploads/dokter/' . $row['foto']);
            $pdo->prepare("UPDATE dokter SET id_spesialis=?,nama_dokter=?,gender=?,no_hp=?,email=?,str_no=?,biaya_konsultasi=?,foto=? WHERE id_dokter=?")->execute([$id_spesialis,$nama,$gender,$hp,$email,$str,$biaya,$foto,$id]);
        } else {
            $pdo->prepare("UPDATE dokter SET id_spesialis=?,nama_dokter=?,gender=?,no_hp=?,email=?,str_no=?,biaya_konsultasi=? WHERE id_dokter=?")->execute([$id_spesialis,$nama,$gender,$hp,$email,$str,$biaya,$id]);
        }
        set_flash('success', 'Dokter berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("SELECT foto FROM dokter WHERE id_dokter=?");
    $stmt->execute([$id]); $row = $stmt->fetch();
    if ($row['foto'] && file_exists(APP_ROOT . '/uploads/dokter/' . $row['foto'])) unlink(APP_ROOT . '/uploads/dokter/' . $row['foto']);
    $pdo->prepare("DELETE FROM dokter WHERE id_dokter=?")->execute([$id]);
    set_flash('success', 'Dokter berhasil dihapus.');
    redirect('');
}

$list = $pdo->query("SELECT d.*, s.nama_spesialis FROM dokter d LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis ORDER BY d.nama_dokter")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user-md me-2"></i>Daftar Dokter</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama</th><th>Spesialis</th><th>Gender</th><th>No HP</th><th>Biaya Konsultasi</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_dokter']) ?></td>
                    <td><?= e($r['nama_dokter']) ?></td>
                    <td><?= e($r['nama_spesialis']) ?></td>
                    <td><?= e($r['gender']) ?></td>
                    <td><?= e($r['no_hp']) ?></td>
                    <td><?= rupiah($r['biaya_konsultasi']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($r['id_dokter']) ?>" data-spesialis="<?= e($r['id_spesialis']) ?>" data-nama="<?= e($r['nama_dokter']) ?>" data-gender="<?= e($r['gender']) ?>" data-hp="<?= e($r['no_hp']) ?>" data-email="<?= e($r['email']) ?>" data-str="<?= e($r['str_no']) ?>" data-biaya="<?= e($r['biaya_konsultasi']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_dokter']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus dokter ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modalForm" tabindex="-1"><div class="modal-dialog">
    <form method="post" enctype="multipart/form-data" class="modal-content">
        <?= csrf_field() ?>
        <input type="hidden" name="act" id="act" value="tambah">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Dokter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Spesialis</label><select name="id_spesialis" id="id_spesialis" class="form-select" required><?php foreach($spesialis_list as $s): ?><option value="<?= e($s['id_spesialis']) ?>"><?= e($s['nama_spesialis']) ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label">Nama Dokter</label><input type="text" name="nama_dokter" id="nama_dokter" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Gender</label><select name="gender" id="gender" class="form-select"><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select></div>
            <div class="mb-3"><label class="form-label">No HP</label><input type="text" name="no_hp" id="no_hp" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="email" class="form-control"></div>
            <div class="mb-3"><label class="form-label">No STR</label><input type="text" name="str_no" id="str_no" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Biaya Konsultasi</label><input type="number" step="500" name="biaya_konsultasi" id="biaya_konsultasi" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Foto</label><input type="file" name="foto" id="foto" class="form-control" accept="image/*"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget,id=btn.getAttribute('data-id');
    if(id){document.getElementById('act').value='edit';document.getElementById('id').value=id;document.getElementById('id_spesialis').value=btn.getAttribute('data-spesialis');document.getElementById('nama_dokter').value=btn.getAttribute('data-nama');document.getElementById('gender').value=btn.getAttribute('data-gender');document.getElementById('no_hp').value=btn.getAttribute('data-hp');document.getElementById('email').value=btn.getAttribute('data-email');document.getElementById('str_no').value=btn.getAttribute('data-str');document.getElementById('biaya_konsultasi').value=btn.getAttribute('data-biaya');document.getElementById('modalTitle').textContent='Edit Dokter';}
    else{document.getElementById('act').value='tambah';document.getElementById('id').value='';document.getElementById('nama_dokter').value='';document.getElementById('modalTitle').textContent='Tambah Dokter';}
});
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
