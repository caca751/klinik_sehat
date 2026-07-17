<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Detail Pesanan';
$active = 'pesanan';

$id = (int)($_GET['id'] ?? 0);
$pes = $pdo->prepare("SELECT p.*, u.nama, u.email, u.no_hp, u.alamat FROM pesanan p JOIN users u ON u.id_user=p.id_user WHERE p.id_pesanan=?");
$pes->execute([$id]);
$pes = $pes->fetch();
if (!$pes) { set_flash('error', 'Pesanan tidak ditemukan.'); redirect(BASE_URL . 'admin/pesanan/'); }

$items = $pdo->prepare("SELECT d.*, o.nama_obat, o.gambar FROM detail_pesanan d JOIN obat o ON o.id_obat=d.id_obat WHERE d.id_pesanan=?");
$items->execute([$id]);

$pay = $pdo->prepare("SELECT pb.*, mp.nama_metode AS nama_metode, mp.nomor AS metode_nomor, mp.pemilik AS metode_pemilik, mp.tipe AS metode_tipe FROM pembayaran pb LEFT JOIN metode_pembayaran mp ON mp.id_metode = pb.id_metode WHERE pb.id_pesanan=?");
$pay->execute([$id]); $pay = $pay->fetch();

$ship = $pdo->prepare("SELECT * FROM pengiriman WHERE id_pesanan=?");
$ship->execute([$id]); $ship = $ship->fetch();

require_once '../../includes/header_admin.php';
?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="fas fa-receipt me-2"></i><?= e($pes['kode_pesanan']) ?></span> <?= status_badge($pes['status']) ?></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Obat</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        <?php $gt = 0; foreach ($items as $it): $gt += $it['subtotal']; ?>
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
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-summary">
            <div class="card-header">Update Status</div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>admin/pesanan/proses.php">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $pes['id_pesanan'] ?>">
                    <select name="status" class="form-select mb-2">
                        <?php foreach (['Menunggu Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan'] as $s): ?>
                            <option value="<?= $s ?>" <?= $pes['status']===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary w-100">Simpan Status</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Info Customer</div>
            <div class="card-body">
                <p class="mb-1"><strong><?= e($pes['nama']) ?></strong></p>
                <p class="mb-1 small"><?= e($pes['email']) ?></p>
                <p class="mb-1 small"><?= e($pes['no_hp']) ?></p>
                <p class="mb-0 small"><?= e($pes['alamat']) ?></p>
            </div>
        </div>
        <?php if ($pay): ?>
        <div class="card">
            <div class="card-header">Pembayaran</div>
            <div class="card-body">
                <p class="mb-1">Metode: <strong><?= e($pay['nama_metode'] ?? '-') ?></strong></p>
                <p class="mb-1">Tujuan: <span><?php if (!empty($pay['id_metode']) && !empty($pay['metode_nomor'])): ?><?= e($pay['metode_tipe']==='bank' ? 'Rekening Bank' : 'E-Wallet') ?> <?= e($pay['metode_nomor']) ?> a.n. <?= e($pay['metode_pemilik']) ?><?php else: ?>-<?php endif; ?></span></p>
                <p class="mb-1">Status: <?= status_badge($pay['status']) ?></p>
                <?php if (!empty($pay['bukti_transfer'])): ?><img src="<?= URL_BUKTI . e($pay['bukti_transfer']) ?>" class="img-fluid rounded" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'"><p class="small mt-1"><?= e($pay['bukti_transfer']) ?></p><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">Pengiriman</div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>admin/pengiriman/proses.php">
                    <?= csrf_field() ?><input type="hidden" name="id_pesanan" value="<?= $pes['id_pesanan'] ?>">
                    <div class="mb-2"><input type="text" name="ekspedisi" class="form-control" placeholder="Ekspedisi" value="<?= e($ship['ekspedisi'] ?? '') ?>"></div>
                    <div class="mb-2"><input type="text" name="nomor_resi" class="form-control" placeholder="No. Resi" value="<?= e($ship['nomor_resi'] ?? '') ?>"></div>
                    <select name="status" class="form-select mb-2">
                        <?php foreach (['Dikemas','Dikirim','Diterima','Gagal'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($ship['status'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-success w-100">Simpan Pengiriman</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>
