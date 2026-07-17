<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Jadwal Praktik';
$active = 'jadwal';
$dokters = $pdo->query("SELECT d.id_dokter, d.nama_dokter, s.nama_spesialis FROM dokter d LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis ORDER BY d.nama_dokter")->fetchAll();
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();
$kliniks = $pdo->query("SELECT k.*, kt.nama_kota FROM klinik k LEFT JOIN kota kt ON kt.id_kota=k.id_kota ORDER BY k.nama_klinik")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $id_dokter = (int)$_POST['id_dokter'];
    $id_klinik = (int)$_POST['id_klinik'];
    $hari = clean($_POST['hari']);
    $jam_mulai = clean($_POST['jam_mulai']);
    $jam_selesai = clean($_POST['jam_selesai']);
    $kuota = (int)$_POST['kuota'];
    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO jadwal_praktik (id_dokter,id_klinik,hari,jam_mulai,jam_selesai,kuota) VALUES (?,?,?,?,?,?)")->execute([$id_dokter,$id_klinik,$hari,$jam_mulai,$jam_selesai,$kuota]);
        set_flash('success', 'Jadwal berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE jadwal_praktik SET id_dokter=?,id_klinik=?,hari=?,jam_mulai=?,jam_selesai=?,kuota=? WHERE id_jadwal=?")->execute([$id_dokter,$id_klinik,$hari,$jam_mulai,$jam_selesai,$kuota,$id]);
        set_flash('success', 'Jadwal berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM jadwal_praktik WHERE id_jadwal=?")->execute([$id]);
    set_flash('success', 'Jadwal berhasil dihapus.');
    redirect('');
}

$list = $pdo->query("SELECT j.*, d.nama_dokter, s.nama_spesialis, k.nama_klinik, kt.nama_kota FROM jadwal_praktik j LEFT JOIN dokter d ON d.id_dokter=j.id_dokter LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis LEFT JOIN klinik k ON k.id_klinik=j.id_klinik LEFT JOIN kota kt ON kt.id_kota=k.id_kota ORDER BY j.hari, j.jam_mulai")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-alt me-2"></i>Daftar Jadwal Praktik</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Dokter</th><th>Spesialis</th><th>Klinik</th><th>Kota</th><th>Hari</th><th>Jam</th><th>Kuota</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_jadwal']) ?></td>
                    <td><?= e($r['nama_dokter']) ?></td>
                    <td><?= e($r['nama_spesialis']) ?></td>
                    <td><?= e($r['nama_klinik']) ?></td>
                    <td><?= e($r['nama_kota']) ?></td>
                    <td><?= e($r['hari']) ?></td>
                    <td><?= e($r['jam_mulai']) ?> - <?= e($r['jam_selesai']) ?></td>
                    <td><?= e($r['kuota']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" data-id="<?= e($r['id_jadwal']) ?>" data-dokter="<?= e($r['id_dokter']) ?>" data-klinik="<?= e($r['id_klinik']) ?>" data-hari="<?= e($r['hari']) ?>" data-mulai="<?= e($r['jam_mulai']) ?>" data-selesai="<?= e($r['jam_selesai']) ?>" data-kuota="<?= e($r['kuota']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_jadwal']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jadwal ini?')"><i class="fas fa-trash"></i></a>
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
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Dokter</label><select name="id_dokter" id="id_dokter" class="form-select" required><?php foreach($dokters as $d): ?><option value="<?= e($d['id_dokter']) ?>"><?= e($d['nama_dokter']) ?> (<?= e($d['nama_spesialis']) ?>)</option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Klinik</label><select name="id_klinik" id="id_klinik" class="form-select" required><?php foreach($kliniks as $k): ?><option value="<?= e($k['id_klinik']) ?>"><?= e($k['nama_klinik']) ?> (<?= e($k['nama_kota']) ?>)</option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label">Hari</label><select name="hari" id="hari" class="form-select"><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option></select></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Jam Mulai</label><input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Jam Selesai</label><input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Kuota</label><input type="number" name="kuota" id="kuota" class="form-control" value="1" min="1"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget,id=btn.getAttribute('data-id');
    if(id){document.getElementById('act').value='edit';document.getElementById('id').value=id;document.getElementById('id_dokter').value=btn.getAttribute('data-dokter');document.getElementById('id_klinik').value=btn.getAttribute('data-klinik');document.getElementById('hari').value=btn.getAttribute('data-hari');document.getElementById('jam_mulai').value=btn.getAttribute('data-mulai');document.getElementById('jam_selesai').value=btn.getAttribute('data-selesai');document.getElementById('kuota').value=btn.getAttribute('data-kuota');document.getElementById('modalTitle').textContent='Edit Jadwal';}
    else{document.getElementById('act').value='tambah';document.getElementById('id').value='';document.getElementById('modalTitle').textContent='Tambah Jadwal';}
});
</script>
<?php require_once '../../includes/footer_admin.php'; ?>
