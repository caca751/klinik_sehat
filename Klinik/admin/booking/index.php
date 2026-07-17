<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Booking';
$active = 'booking';

$list = $pdo->query("SELECT b.*, u.nama as nama_pasien, d.nama_dokter, s.nama_spesialis, k.nama_klinik, kt.nama_kota FROM booking b LEFT JOIN users u ON u.id_user=b.id_user LEFT JOIN dokter d ON d.id_dokter=b.id_dokter LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis LEFT JOIN klinik k ON k.id_klinik=b.id_klinik LEFT JOIN kota kt ON kt.id_kota=k.id_kota ORDER BY b.created_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $status = clean($_POST['status']);
    if ($act === 'update_status') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE booking SET status=? WHERE id_booking=?")->execute([$status, $id]);
        set_flash('success', 'Status booking berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM booking WHERE id_booking=?")->execute([$id]);
    set_flash('success', 'Booking berhasil dihapus.');
    redirect('');
}

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-calendar-check me-2"></i>Daftar Booking Pasien</div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Kode</th><th>Pasien</th><th>Dokter</th><th>Spesialis</th><th>Klinik</th><th>Kota</th><th>Tanggal</th><th>Keluhan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['kode_booking']) ?></td>
                    <td><?= e($r['nama_pasien']) ?></td>
                    <td><?= e($r['nama_dokter']) ?></td>
                    <td><?= e($r['nama_spesialis']) ?></td>
                    <td><?= e($r['nama_klinik']) ?></td>
                    <td><?= e($r['nama_kota']) ?></td>
                    <td><?= tgl_indo($r['tanggal_booking']) ?></td>
                    <td><?= e($r['keluhan']) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td class="text-end">
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="act" value="update_status">
                            <input type="hidden" name="id" value="<?= e($r['id_booking']) ?>">
                            <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                <option value="Menunggu" <?= $r['status']==='Menunggu'?'selected':'' ?>>Menunggu</option>
                                <option value="Selesai" <?= $r['status']==='Selesai'?'selected':'' ?>>Selesai</option>
                                <option value="Dibatalkan" <?= $r['status']==='Dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                            </select>
                        </form>
                        <a href="?hapus=<?= e($r['id_booking']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus booking ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>
