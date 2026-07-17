<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Detail Pesanan';
$active = 'pesanan';
$uid = $_SESSION['user']['id_user'];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan=? AND id_user=?");
$stmt->execute([$id, $uid]);
$pes = $stmt->fetch();
if (!$pes) { set_flash('error', 'Pesanan tidak ditemukan.'); redirect(BASE_URL . 'user/pesanan/'); }

$items = $pdo->prepare("SELECT d.*, o.nama_obat, o.gambar FROM detail_pesanan d JOIN obat o ON o.id_obat=d.id_obat WHERE d.id_pesanan=?");
$items->execute([$id]);

$pay = $pdo->prepare("SELECT pb.*, mp.nama_metode AS nama_metode, mp.nomor AS metode_nomor, mp.pemilik AS metode_pemilik, mp.tipe AS metode_tipe FROM pembayaran pb LEFT JOIN metode_pembayaran mp ON mp.id_metode = pb.id_metode WHERE pb.id_pesanan=?");
$pay->execute([$id]); $pay = $pay->fetch();

$ship = $pdo->prepare("SELECT * FROM pengiriman WHERE id_pesanan=?");
$ship->execute([$id]); $ship = $ship->fetch();

/* Proses konfirmasi pembayaran */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_bayar'])) {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    if ($pes['status'] !== 'Menunggu Pembayaran') { set_flash('error', 'Pesanan sudah diproses.'); redirect(''); }
    
    try {
        $pdo->beginTransaction();
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE pembayaran SET status='Lunas', tanggal_bayar=NOW() WHERE id_pesanan=?")->execute([$id]);
        $pdo->prepare("UPDATE pesanan SET status='Diproses' WHERE id_pesanan=?")->execute([$id]);
        $pdo->commit();
        
        send_notif($pdo, $uid, 'Pembayaran Dikonfirmasi', "Pesanan {$pes['kode_pesanan']} telah dibayar dan sedang diproses.");
        redirect(BASE_URL . 'user/pembayaran/sukses.php?id=' . $id);
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Gagal konfirmasi pembayaran: ' . $e->getMessage());
        redirect('');
    }
}

/* Proses pembatalan */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batalkan_pesanan'])) {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    if ($pes['status'] !== 'Menunggu Pembayaran') { set_flash('error', 'Pesanan tidak dapat dibatalkan.'); redirect(''); }
    
    try {
        $pdo->beginTransaction();
        
        // Restore stok obat dan apotek
        $detailItems = $pdo->prepare("SELECT * FROM detail_pesanan WHERE id_pesanan=?");
        $detailItems->execute([$id]);
        foreach ($detailItems as $d) {
            $pdo->prepare("UPDATE obat SET stok = stok + ? WHERE id_obat=?")->execute([$d['jumlah'], $d['id_obat']]);
            
            if ($d['id_apotek'] > 0) {
                $pdo->prepare("UPDATE harga_stok_apotek SET stok = stok + ? WHERE id_obat=? AND id_apotek=?")->execute([$d['jumlah'], $d['id_obat'], $d['id_apotek']]);
            }
        }
        
        // Hapus data pesanan
        $pdo->prepare("DELETE FROM pembayaran WHERE id_pesanan=?")->execute([$id]);
        $pdo->prepare("DELETE FROM detail_pesanan WHERE id_pesanan=?")->execute([$id]);
        $pdo->prepare("DELETE FROM pesanan WHERE id_pesanan=?")->execute([$id]);
        
        $pdo->commit();
        
        send_notif($pdo, $uid, 'Pesanan Dibatalkan', "Pesanan {$pes['kode_pesanan']} telah dibatalkan.");
        set_flash('success', 'Pesanan berhasil dibatalkan.');
        redirect(BASE_URL . 'user/obat/');
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        redirect('');
    }
}

function img_u($g) { return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg'; }
$qr = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($pes['kode_pesanan']);
require_once '../../includes/header_user.php';
?>
<style>
    .pesanan-actions form { display: inline-block; margin: 0; }
    .pesanan-actions .btn-action {
        min-width: 140px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="fas fa-receipt me-2"></i><?= e($pes['kode_pesanan']) ?></span> <?= status_badge($pes['status']) ?></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Obat</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= e($it['nama_obat']) ?></td>
                                <td><?= rupiah($it['harga']) ?></td>
                                <td><?= $it['jumlah'] ?></td>
                                <td class="text-end"><?= rupiah($it['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= rupiah($pes['total']) ?></th></tr></tfoot>
                    </table>
                </div>
                <div class="d-flex gap-2 flex-wrap pesanan-actions">
                    <?php if ($pes['status'] === 'Menunggu Pembayaran'): ?>
                        <form method="post" onsubmit="return confirm('Konfirmasi bahwa Anda sudah melakukan pembayaran?')">
                            <?= csrf_field() ?>
                            <button type="submit" name="konfirmasi_bayar" class="btn btn-success btn-action"><i class="fas fa-check me-1"></i> Saya Sudah Bayar</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.')">
                            <?= csrf_field() ?>
                            <button type="submit" name="batalkan_pesanan" class="btn btn-danger btn-action"><i class="fas fa-times me-1"></i> Batalkan</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>user/review/?id_pesanan=<?= $pes['id_pesanan'] ?>" class="btn btn-outline-primary btn-action">Beri Review</a>
                        <?php if ($ship): ?><a href="<?= BASE_URL ?>user/tracking/?kode=<?= e($pes['kode_pesanan']) ?>" class="btn btn-outline-info btn-action">Tracking</a><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($ship): ?>
        <div class="card">
            <div class="card-header">Info Pengiriman</div>
            <div class="card-body">
                <p>Ekspedisi: <strong><?= e($ship['ekspedisi'] ?? '-') ?></strong></p>
                <p>No. Resi: <strong><?= e($ship['nomor_resi'] ?? '-') ?></strong></p>
                <p>Status: <?= status_badge($ship['status']) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4 sticky-summary">
        <div class="card text-center">
            <div class="card-header">QR Code Pesanan</div>
            <div class="card-body">
                <img src="<?= $qr ?>" width="160" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'">
                <p class="small text-muted mt-2"><?= e($pes['kode_pesanan']) ?></p>
            </div>
        </div>
        <?php if ($pay): ?>
        <div class="card border-success">
            <div class="card-header bg-success text-white">Pembayaran</div>
            <div class="card-body">
                <p>Metode: <strong><?= e($pay['nama_metode'] ?? '-') ?></strong></p>
                <p>Status: <?= status_badge($pay['status']) ?></p>
                <?php if (!empty($pay['id_metode']) && !empty($pay['metode_nomor'])): ?>
                <div class="alert alert-warning small mb-2">
                    <strong>Instruksi Pembayaran:</strong><br>
                    <?= e($pay['metode_tipe']==='bank' ? 'Transfer ke rekening Bank ' . e($pay['nama_metode']) : 'Bayar via ' . e($pay['nama_metode'])) ?>:<br>
                    <strong><?= e($pay['metode_nomor']) ?></strong> a.n. <strong><?= e($pay['metode_pemilik']) ?></strong><br>
                    Total yang harus dibayar: <strong><?= rupiah($pes['total']) ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
