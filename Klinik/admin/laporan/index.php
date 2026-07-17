<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Laporan';
$active = 'laporan';

$r = $_GET['r'] ?? 'penjualan';
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

$reports = [
    'penjualan' => 'Laporan Penjualan',
    'pendapatan' => 'Laporan Pendapatan',
    'stok' => 'Laporan Stok',
    'user' => 'Laporan User',
    'terlaris' => 'Laporan Obat Terlaris',
];

$rows = [];
$total = 0;
if ($r === 'penjualan') {
    $stmt = $pdo->prepare("SELECT p.*, u.nama FROM pesanan p JOIN users u ON u.id_user=p.id_user WHERE DATE(p.tanggal) BETWEEN ? AND ? ORDER BY p.tanggal DESC");
    $stmt->execute([$from, $to]); $rows = $stmt->fetchAll();
    foreach ($rows as $x) $total += $x['total'];
} elseif ($r === 'pendapatan') {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%Y-%m') bln, COUNT(*) jml, SUM(total) tot FROM pesanan WHERE status IN ('Diproses','Dikirim','Selesai') AND DATE(tanggal) BETWEEN ? AND ? GROUP BY bln ORDER BY bln");
    $stmt->execute([$from, $to]); $rows = $stmt->fetchAll();
    foreach ($rows as $x) $total += $x['tot'];
} elseif ($r === 'stok') {
    $rows = $pdo->query("SELECT o.kode_obat, o.nama_obat, h.harga, h.stok FROM harga_stok_apotek h JOIN obat o ON o.id_obat=h.id_obat ORDER BY h.stok ASC")->fetchAll();
} elseif ($r === 'user') {
    $rows = $pdo->query("SELECT * FROM users WHERE role='customer' ORDER BY id_user")->fetchAll();
} elseif ($r === 'terlaris') {
    $stmt = $pdo->prepare("SELECT o.kode_obat, o.nama_obat, k.nama_kategori, SUM(d.jumlah) terjual, SUM(d.subtotal) pendapatan FROM detail_pesanan d JOIN obat o ON o.id_obat=d.id_obat LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori GROUP BY d.id_obat ORDER BY terjual DESC");
    $stmt->execute(); $rows = $stmt->fetchAll();
}

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="fas fa-file-alt me-2"></i>Laporan</span>
        <div>
            <a href="?r=penjualan" class="btn btn-sm btn-<?= $r==='penjualan'?'primary':'outline-primary' ?>">Penjualan</a>
            <a href="?r=pendapatan" class="btn btn-sm btn-<?= $r==='pendapatan'?'primary':'outline-primary' ?>">Pendapatan</a>
            <a href="?r=stok" class="btn btn-sm btn-<?= $r==='stok'?'primary':'outline-primary' ?>">Stok</a>
            <a href="?r=user" class="btn btn-sm btn-<?= $r==='user'?'primary':'outline-primary' ?>">User</a>
            <a href="?r=terlaris" class="btn btn-sm btn-<?= $r==='terlaris'?'primary':'outline-primary' ?>">Terlaris</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end mb-3 no-print">
            <input type="hidden" name="r" value="<?= e($r) ?>">
            <div class="col-auto"><label class="form-label">Dari</label><input type="date" name="from" class="form-control" value="<?= e($from) ?>"></div>
            <div class="col-auto"><label class="form-label">Sampai</label><input type="date" name="to" class="form-control" value="<?= e($to) ?>"></div>
            <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak / PDF</button>
                <a href="<?= BASE_URL ?>admin/laporan/export.php?r=<?= e($r) ?>&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
            </div>
        </form>

        <h5 class="mb-3 d-none d-print-block"><?= e($reports[$r]) ?> (<?= tgl_indo($from) ?> - <?= tgl_indo($to) ?>)</h5>

        <div class="table-responsive">
        <?php if ($r === 'penjualan'): ?>
            <table class="table table-bordered">
                <thead><tr><th>No</th><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $i => $x): ?><tr>
                    <td><?= $i+1 ?></td><td><?= e($x['kode_pesanan']) ?></td><td><?= e($x['nama']) ?></td><td><?= tgl_waktu($x['tanggal']) ?></td><td><?= rupiah($x['total']) ?></td><td><?= e($x['status']) ?></td>
                </tr><?php endforeach; ?>
                <tr><th colspan="4">Total</th><th colspan="2"><?= rupiah($total) ?></th></tr>
                </tbody>
            </table>
        <?php elseif ($r === 'pendapatan'): ?>
            <table class="table table-bordered">
                <thead><tr><th>Bulan</th><th>Jumlah Pesanan</th><th>Pendapatan</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $x): ?><tr><td><?= e($x['bln']) ?></td><td><?= $x['jml'] ?></td><td><?= rupiah($x['tot']) ?></td></tr><?php endforeach; ?>
                <tr><th colspan="2">Total</th><th><?= rupiah($total) ?></th></tr>
                </tbody>
            </table>
        <?php elseif ($r === 'stok'): ?>
            <table class="table table-bordered">
                <thead><tr><th>Kode</th><th>Nama Obat</th><th>Harga</th><th>Stok</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $x): ?><tr><td><?= e($x['kode_obat']) ?></td><td><?= e($x['nama_obat']) ?></td><td><?= rupiah($x['harga']) ?></td><td><?= $x['stok'] ?></td></tr><?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($r === 'user'): ?>
            <table class="table table-bordered">
                <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Alamat</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $i => $x): ?><tr><td><?= $i+1 ?></td><td><?= e($x['nama']) ?></td><td><?= e($x['email']) ?></td><td><?= e($x['no_hp']) ?></td><td><?= e($x['alamat']) ?></td></tr><?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($r === 'terlaris'): ?>
            <table class="table table-bordered">
                <thead><tr><th>Kode</th><th>Nama Obat</th><th>Kategori</th><th>Terjual</th><th>Pendapatan</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $x): ?><tr><td><?= e($x['kode_obat']) ?></td><td><?= e($x['nama_obat']) ?></td><td><?= e($x['nama_kategori']) ?></td><td><?= $x['terjual'] ?></td><td><?= rupiah($x['pendapatan']) ?></td></tr><?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>
