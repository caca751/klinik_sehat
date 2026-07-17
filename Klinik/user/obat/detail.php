<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Detail Obat';
$active = 'obat';

$id = (int)($_GET['id'] ?? 0);
$selectedApotek = isset($_GET['id_apotek']) ? (int)$_GET['id_apotek'] : (isset($_GET['apotek']) ? (int)$_GET['apotek'] : 0);
if ($selectedApotek > 0) {
    $stmt = $pdo->prepare("SELECT o.*, k.nama_kategori, s.nama_supplier, hsa.harga AS apotek_harga, hsa.stok AS apotek_stok FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier LEFT JOIN harga_stok_apotek hsa ON hsa.id_obat=o.id_obat AND hsa.id_apotek=? WHERE o.id_obat=?");
    $stmt->execute([$selectedApotek, $id]);
} else {
    $stmt = $pdo->prepare("SELECT o.*, k.nama_kategori, s.nama_supplier FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier WHERE o.id_obat=?");
    $stmt->execute([$id]);
}
$obat = $stmt->fetch();
if (!$obat) { set_flash('error', 'Obat tidak ditemukan.'); redirect(BASE_URL . 'user/obat/'); }

$reviews = $pdo->prepare("SELECT r.*, u.nama FROM review r JOIN users u ON u.id_user=r.id_user WHERE r.id_obat=? ORDER BY r.tanggal DESC LIMIT 5");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();
$avg = $pdo->prepare("SELECT AVG(rating) avg_r, COUNT(*) c FROM review WHERE id_obat=?");
$avg->execute([$id]); $avg = $avg->fetch();

$img = $obat['gambar'] ? URL_OBAT . e($obat['gambar']) : BASE_URL . 'assets/images/no-image.svg';
$apotekHarga = $obat['apotek_harga'] ?? null;
$apotekStok = isset($obat['apotek_stok']) ? (int)$obat['apotek_stok'] : null;
$hargaTampil = $apotekHarga !== null ? rupiah($apotekHarga) : rupiah($obat['harga']);
if ($selectedApotek > 0 && $apotekHarga === null) {
    $stokText = 'Tidak tersedia di apotek ini';
    $stokClass = 'text-danger';
    $canAddCart = false;
} else {
    $stokValue = $apotekHarga !== null ? $apotekStok : (int)$obat['stok'];
    $stokText = $stokValue > 0 ? $stokValue . ' tersedia' : 'Habis';
    $stokClass = $stokValue > 0 ? 'text-success' : 'text-danger';
    $canAddCart = $stokValue > 0;
}
require_once '../../includes/header_user.php';
?>
<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <img src="<?= $img ?>" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'" class="card-img-top p-3" style="height:320px;object-fit:contain;background:var(--primary-light)">
            <div class="card-body text-center">
                <h5 class="fw-bold"><?= e($obat['nama_obat']) ?></h5>
                <div class="text-warning"><?= str_repeat('★', round($avg['avg_r'] ?? 0)) ?: '☆☆☆☆☆' ?> <small class="text-muted">(<?= $avg['c'] ?> review)</small></div>
                <h3 class="text-primary my-2"><?= $hargaTampil ?></h3>
                <p>Stok: <strong class="<?= $stokClass ?>"><?= $stokText ?></strong></p>
                <?php if ($canAddCart): ?><button class="btn btn-primary w-100" onclick="addToCart(<?= $obat['id_obat'] ?>, <?= $selectedApotek ?>)"><i class="fas fa-cart-plus me-1"></i> Tambah ke Keranjang</button><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Informasi Obat</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th width="140">Kode</th><td><?= e($obat['kode_obat']) ?></td></tr>
                    <tr><th>Kategori</th><td><?= e($obat['nama_kategori']) ?></td></tr>
                    <tr><th>Supplier</th><td><?= e($obat['nama_supplier']) ?></td></tr>
                    <tr><th>Tanggal Expired</th><td><?= e($obat['tanggal_expired']) ?></td></tr>
                </table>
                <h6 class="mt-3">Deskripsi</h6>
                <p class="text-muted-2"><?= e($obat['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Review Pelanggan</div>
            <div class="card-body">
                <?php if (empty($reviews)): ?><p class="text-muted">Belum ada review.</p><?php endif; ?>
                <?php foreach ($reviews as $rv): ?>
                <div class="mb-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between"><strong><?= e($rv['nama']) ?></strong><span class="text-warning"><?= str_repeat('★', $rv['rating']) ?></span></div>
                    <div class="small text-muted-2"><?= e($rv['komentar']) ?></div>
                </div>
                <?php endforeach; ?>
                <a href="<?= BASE_URL ?>user/review/?id_obat=<?= $obat['id_obat'] ?>" class="btn btn-sm btn-outline-primary mt-2">Beri Review</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
