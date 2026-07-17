<?php
require_once '../../config/koneksi.php';
require_login();
$page_title = 'Booking Saya';
$active = 'booking';
$user = current_user();
$bookings = $pdo->prepare("SELECT b.*, d.nama_dokter, s.nama_spesialis, k.nama_klinik, kt.nama_kota FROM booking b LEFT JOIN dokter d ON d.id_dokter=b.id_dokter LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis LEFT JOIN klinik k ON k.id_klinik=b.id_klinik LEFT JOIN kota kt ON kt.id_kota=k.id_kota WHERE b.id_user=? ORDER BY b.created_at DESC");
$bookings->execute([$user['id_user']]);
$bookings = $bookings->fetchAll();
require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-calendar-check me-2"></i>Daftar Booking Saya</div>
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Kode</th><th>Dokter</th><th>Spesialis</th><th>Klinik</th><th>Kota</th><th>Tanggal</th><th>Keluhan</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= e($b['kode_booking']) ?></td>
                    <td><?= e($b['nama_dokter']) ?></td>
                    <td><?= e($b['nama_spesialis']) ?></td>
                    <td><?= e($b['nama_klinik']) ?></td>
                    <td><?= e($b['nama_kota']) ?></td>
                    <td><?= tgl_indo($b['tanggal_booking']) ?></td>
                    <td><?= e($b['keluhan']) ?></td>
                    <td><?= status_badge($b['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bookings)) echo '<tr><td colspan="8" class="text-center text-muted">Belum ada booking.</td></tr>'; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
