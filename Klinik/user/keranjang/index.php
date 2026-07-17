<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Keranjang';
$active = 'obat';
$uid = $_SESSION['user']['id_user'];

$stmt = $pdo->prepare("SELECT k.*, o.nama_obat, o.harga, o.gambar, o.stok, a.nama_apotek, a.alamat as alamat_apotek FROM keranjang k JOIN obat o ON o.id_obat=k.id_obat LEFT JOIN apotek a ON a.id_apotek=k.id_apotek WHERE k.id_user=? ORDER BY k.id_keranjang DESC");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $it) $total += $it['harga'] * $it['jumlah'];

function img_u($g) { return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg'; }
require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-cart-shopping me-2"></i>Keranjang Belanja</div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="empty-state"><i class="fas fa-cart-shopping"></i><p>Keranjang Anda kosong.</p>
                <a href="<?= BASE_URL ?>user/obat/" class="btn btn-primary">Belanja Sekarang</a></div>
        <?php else: ?>
        <div id="cartList">
            <?php foreach ($items as $it): ?>
            <div class="cart-item">
                <img src="<?= img_u($it['gambar']) ?>" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'">
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($it['nama_obat']) ?></div>
                    <div class="text-primary"><?= rupiah($it['harga']) ?></div>
                    <?php if ($it['nama_apotek']): ?>
                    <div class="small text-muted"><i class="fas fa-building me-1"></i><?= e($it['nama_apotek']) ?> - <?= e($it['alamat_apotek']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="qty-box">
                    <button type="button" onclick="changeQty(<?= $it['id_keranjang'] ?>, -1, this.parentNode.querySelector('input'))">-</button>
                    <input type="number" value="<?= $it['jumlah'] ?>" min="1" max="<?= $it['stok'] ?>" readonly>
                    <button type="button" onclick="changeQty(<?= $it['id_keranjang'] ?>, 1, this.parentNode.querySelector('input'))">+</button>
                </div>
                <div class="fw-bold" style="width:110px;text-align:right"><?= rupiah($it['harga'] * $it['jumlah']) ?></div>
                <button class="btn btn-sm btn-outline-danger" onclick="removeCart(<?= $it['id_keranjang'] ?>)"><i class="fas fa-trash"></i></button>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <h5>Total: <span class="text-primary"><?= rupiah($total) ?></span></h5>
            <div>
                <a href="<?= BASE_URL ?>user/obat/" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Lanjut Belanja</a>
                <a href="<?= BASE_URL ?>user/checkout/" class="btn btn-primary"><i class="fas fa-credit-card me-1"></i> Checkout</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
