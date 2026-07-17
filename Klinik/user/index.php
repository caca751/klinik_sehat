<?php
require_once '../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Dashboard';
$active = 'dashboard';
$uid = $_SESSION['user']['id_user'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user=?");
$stmt->execute([$uid]);
$totalPesanan = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user=? AND status IN ('Menunggu Pembayaran','Diproses')");
$stmt->execute([$uid]);
$diproses = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_user=? AND status='Selesai'");
$stmt->execute([$uid]);
$selesai = (int)$stmt->fetchColumn();

$cart = cart_count($pdo, $uid);

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_user=? ORDER BY tanggal DESC LIMIT 5");
$stmt->execute([$uid]);
$recent = $stmt->fetchAll();

$rekom = $pdo->query("SELECT * FROM obat ORDER BY RAND() LIMIT 4")->fetchAll();

function img_u($g) { return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg'; }
require_once '../includes/header_user.php';
?>
<div class="row g-3">
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info"><div class="value"><?= $totalPesanan ?></div><div class="label">Total Pesanan</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon orange"><i class="fas fa-spinner"></i></div>
            <div class="stat-info"><div class="value"><?= $diproses ?></div><div class="label">Diproses</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon green"><i class="fas fa-check"></i></div>
            <div class="stat-info"><div class="value"><?= $selesai ?></div><div class="label">Selesai</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon red"><i class="fas fa-cart-shopping"></i></div>
            <div class="stat-info"><div class="value"><?= $cart ?></div><div class="label">Keranjang</div></div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="fas fa-clock me-2"></i>Pesanan Terbaru</span>
                <a href="<?= BASE_URL ?>user/pesanan/" class="btn btn-sm btn-primary">Lihat Semua</a></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Kode</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if (empty($recent)): ?><tr><td colspan="4" class="text-center text-muted py-3">Belum ada pesanan</td></tr><?php endif; ?>
                        <?php foreach ($recent as $p): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>user/pesanan/detail.php?id=<?= $p['id_pesanan'] ?>"><?= e($p['kode_pesanan']) ?></a></td>
                                <td><?= tgl_waktu($p['tanggal']) ?></td>
                                <td><?= rupiah($p['total']) ?></td>
                                <td><?= status_badge($p['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-summary">
            <div class="card-header"><i class="fas fa-fire me-2"></i>Obat yang sering dibeli</div>
            <div class="card-body">
                <?php foreach ($rekom as $o): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="<?= img_u($o['gambar']) ?>" width="48" height="48" style="object-fit:cover;border-radius:8px;background:var(--primary-light)">
                    <div class="flex-grow-1">
                        <div class="small fw-semibold"><?= e($o['nama_obat']) ?></div>
                        <div class="small text-primary"><?= rupiah($o['harga']) ?></div>
                    </div>
                    <a href="<?= BASE_URL ?>user/obat/detail.php?id=<?= $o['id_obat'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer_user.php'; ?>
