<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Daftar Apotek';
$active = 'apotek';

$apoteks = $pdo->query("SELECT a.*, kt.nama_kota FROM apotek a LEFT JOIN kota kt ON kt.id_kota=a.id_kota ORDER BY a.nama_apotek")->fetchAll();
require_once '../../includes/header_user.php';
?>
<div class="text-center mb-5">
    <h2 class="section-title fw-bold">Apotek Kami</h2>
    <p class="section-subtitle">Pilih apotek sesuai lokasi Anda untuk melihat obat dan harga terbaru.</p>
</div>
<div class="row g-4">
    <?php foreach ($apoteks as $a): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-building text-primary fs-4"></i>
                    <h6 class="mb-0 klinik-nama fw-bold"><?= e($a['nama_apotek']) ?></h6>
                </div>
                <div class="klinik-info">
                    <p class="mb-1 text-muted"><i class="fas fa-map-marker-alt me-2"></i> <?= e($a['alamat']) ?>, <?= e($a['nama_kota']) ?></p>
                    <p class="mb-1 text-muted"><i class="fas fa-phone me-2"></i> <?= e($a['no_telp']) ?></p>
                    <p class="mb-0 text-muted"><i class="fas fa-envelope me-2"></i> <?= e($a['email']) ?></p>
                </div>
                <a href="<?= BASE_URL ?>user/obat/?apotek=<?= $a['id_apotek'] ?>" class="btn btn-primary w-100 mt-3">Lihat Obat di Apotek Ini</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
