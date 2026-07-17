<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Tracking Pesanan';
$active = 'tracking';
$uid = $_SESSION['user']['id_user'];

$kode = clean($_GET['kode'] ?? '');
$pes = null;
if ($kode) {
    $stmt = $pdo->prepare("SELECT p.*, pg.ekspedisi, pg.nomor_resi, pg.status status_kirim FROM pesanan p LEFT JOIN pengiriman pg ON pg.id_pesanan=p.id_pesanan WHERE p.kode_pesanan=? AND p.id_user=?");
    $stmt->execute([$kode, $uid]);
    $pes = $stmt->fetch();
}
$recent = $pdo->prepare("SELECT kode_pesanan, status FROM pesanan WHERE id_user=? ORDER BY tanggal DESC LIMIT 8");
$recent->execute([$uid]);

$steps = ['Menunggu Pembayaran' => 0, 'Diproses' => 1, 'Dikirim' => 2, 'Selesai' => 3];
function progress($status) {
    $map = ['Menunggu Pembayaran' => 1, 'Diproses' => 2, 'Dikirim' => 3, 'Selesai' => 4, 'Dibatalkan' => 0];
    return $map[$status] ?? 0;
}
require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-box-open me-2"></i>Tracking Pesanan</div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-md-8"><label class="form-label">Masukkan Kode Pesanan</label>
                <input type="text" name="kode" class="form-control" placeholder="PSN-20250928-020" value="<?= e($kode) ?>" required></div>
            <div class="col-md-4"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Lacak</button></div>
        </form>
        <div class="mb-3">
            <small class="text-muted">Pesanan saya:</small>
            <?php foreach ($recent as $r): ?><a href="?kode=<?= e($r['kode_pesanan']) ?>" class="badge bg-light text-dark text-decoration-none me-1"><?= e($r['kode_pesanan']) ?></a><?php endforeach; ?>
        </div>

        <?php if ($kode && !$pes): ?>
            <div class="alert alert-warning">Pesanan dengan kode <strong><?= e($kode) ?></strong> tidak ditemukan.</div>
        <?php elseif ($pes): ?>
            <div class="text-center mb-4">
                <h5><?= e($pes['kode_pesanan']) ?></h5>
                <?= status_badge($pes['status']) ?>
            </div>
            <?php if ($pes['status'] !== 'Dibatalkan'): ?>
            <div class="d-flex justify-content-between position-relative mb-4" style="max-width:600px;margin:auto">
                <?php
                $p = progress($pes['status']);
                $labels = ['Menunggu','Diproses','Dikirim','Selesai'];
                foreach ($labels as $i => $lbl):
                    $done = ($i + 1) <= $p;
                ?>
                <div class="text-center" style="flex:1;z-index:2">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:<?= $done?'#2563eb':'#e5e7eb' ?>;color:<?= $done?'#fff':'#6b7280' ?>">
                        <i class="fas fa-<?= ['clock','gear','truck','check'][$i] ?>"></i>
                    </div>
                    <small class="d-block mt-1"><?= $lbl ?></small>
                </div>
                <?php endforeach; ?>
                <div class="position-absolute top-50 start-0 end-0" style="height:3px;background:#e5e7eb;z-index:1"></div>
            </div>
            <?php else: ?>
            <div class="alert alert-secondary text-center">Pesanan dibatalkan.</div>
            <?php endif; ?>

            <?php if ($pes['nomor_resi']): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-truck me-1"></i> <?= e($pes['ekspedisi']) ?> - No. Resi: <strong><?= e($pes['nomor_resi']) ?></strong> (<?= e($pes['status_kirim']) ?>)
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
