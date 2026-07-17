<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Pembayaran Berhasil';
$active = 'pesanan';
$uid = $_SESSION['user']['id_user'];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, pb.status as status_pembayaran, pb.tanggal_bayar, u.nama, mp.nama_metode AS nama_metode, mp.nomor AS metode_nomor, mp.pemilik AS metode_pemilik, mp.tipe AS metode_tipe FROM pesanan p JOIN users u ON u.id_user=p.id_user LEFT JOIN pembayaran pb ON pb.id_pesanan=p.id_pesanan LEFT JOIN metode_pembayaran mp ON mp.id_metode = pb.id_metode WHERE p.id_pesanan=? AND p.id_user=?");
$stmt->execute([$id, $uid]);
$pes = $stmt->fetch();
if (!$pes) { set_flash('error', 'Pesanan tidak ditemukan.'); redirect(BASE_URL . 'user/pesanan/'); }

$items = $pdo->prepare("SELECT d.*, o.nama_obat FROM detail_pesanan d JOIN obat o ON o.id_obat=d.id_obat WHERE d.id_pesanan=?");
$items->execute([$id]);

$pay = $pdo->prepare("SELECT * FROM pembayaran WHERE id_pesanan=?");
$pay->execute([$id]); $pay = $pay->fetch();

function img_u($g) { return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg'; }
require_once '../../includes/header_user.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card text-center">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                </div>
                <h3 class="fw-bold mb-3">Pembayaran Berhasil!</h3>
                <p class="text-muted">Pesanan Anda telah dikonfirmasi dan sedang diproses.</p>
                
                <div class="card mt-4 text-start">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-file-invoice me-2"></i>INVOICE PEMBAYARAN
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Kode Pesanan</div>
                            <div class="col-7 fw-bold"><?= e($pes['kode_pesanan']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Nama Pelanggan</div>
                            <div class="col-7"><?= e($pes['nama']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Tanggal Pembayaran</div>
                            <div class="col-7"><?= $pay && $pay['tanggal_bayar'] ? date('d F Y H:i', strtotime($pay['tanggal_bayar'])) . ' WIB' : '-' ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Metode Pembayaran</div>
                            <div class="col-7"><?= e($pes['nama_metode'] ?? '-') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Total Bayar</div>
                            <div class="col-7 text-success fw-bold"><?= rupiah($pes['total']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted">Status Pembayaran</div>
                            <div class="col-7"><?= status_badge($pay['status_pembayaran'] ?? 'Menunggu') ?></div>
                        </div>
                        <hr>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Detail Obat</div>
                            <div class="col-7">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($items as $it): ?>
                                        <li><?= e($it['nama_obat']) ?> × <?= $it['jumlah'] ?> = <?= rupiah($it['subtotal']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <a href="<?= BASE_URL ?>user/pesanan/" class="btn btn-primary"><i class="fas fa-list me-1"></i> Lihat Pesanan</a>
                    <a href="<?= BASE_URL ?>user/obat/" class="btn btn-outline-secondary"><i class="fas fa-shopping-bag me-1"></i> Belanja Lagi</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>