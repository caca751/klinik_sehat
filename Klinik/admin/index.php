<?php
require_once '../config/koneksi.php';
require_admin();
$page_title = 'Dashboard';
$active = 'dashboard';

$uid = $_SESSION['user']['id_user'];

/* Statistik */
$totalUser    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalObat    = $pdo->query("SELECT COUNT(*) FROM obat")->fetchColumn();
$totalPesanan = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pesanan WHERE status IN ('Diproses','Dikirim','Selesai')")->fetchColumn();

/* Penjualan 6 bulan terakhir */
$labels = []; $dataPenjualan = [];
for ($i = 5; $i >= 0; $i--) {
    $bln = date('Y-m', strtotime("-$i months"));
    $labels[] = date('M y', strtotime($bln . '-01'));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pesanan WHERE DATE_FORMAT(tanggal,'%Y-%m')=? AND status IN ('Diproses','Dikirim','Selesai')");
    $stmt->execute([$bln]);
    $dataPenjualan[] = (int)$stmt->fetchColumn();
}

/* Obat terlaris */
$stmtTop = $pdo->query("SELECT o.nama_obat, SUM(d.jumlah) qty FROM detail_pesanan d JOIN pesanan p ON p.id_pesanan=d.id_pesanan JOIN obat o ON o.id_obat=d.id_obat WHERE p.status IN ('Menunggu Pembayaran','Diproses','Dikirim','Selesai') GROUP BY d.id_obat ORDER BY qty DESC LIMIT 5");
$topObat = $stmtTop->fetchAll();
$topLabels = array_column($topObat, 'nama_obat');
$topQty   = array_column($topObat, 'qty');

/* Recent orders */
$recent = $pdo->query("SELECT p.*, u.nama FROM pesanan p JOIN users u ON u.id_user=p.id_user ORDER BY p.tanggal DESC LIMIT 5")->fetchAll();

/* Stok hampir habis */
$lowStock = $pdo->query("SELECT o.id_obat, o.nama_obat, h.stok FROM harga_stok_apotek h JOIN obat o ON o.id_obat=h.id_obat WHERE h.stok <= 20 ORDER BY h.stok ASC LIMIT 6")->fetchAll();

require_once '../includes/header_admin.php';
?>
<style>
/* Ensure chart canvas matches card visuals and aligns with other cards */
.card .card-body canvas{ max-height:160px; width:100% !important; display:block; background:transparent; }
.card .card-body{ min-height:160px; }
</style>
<div class="row g-3">
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info"><div class="value"><?= $totalUser ?></div><div class="label">Total Customer</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon green"><i class="fas fa-pills"></i></div>
            <div class="stat-info"><div class="value"><?= $totalObat ?></div><div class="label">Total Obat</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon orange"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info"><div class="value"><?= $totalPesanan ?></div><div class="label">Total Pesanan</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="stat-icon red"><i class="fas fa-money-bill-trend-up"></i></div>
            <div class="stat-info"><div class="value"><?= rupiah($totalPendapatan) ?></div><div class="label">Pendapatan</div></div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="fas fa-chart-line me-2"></i>Grafik Penjualan (6 Bulan)</span></div>
            <div class="card-body"><canvas id="salesChart" height="110"></canvas></div>
        </div>
        <div class="card">
            <div class="card-header"><span><i class="fas fa-receipt me-2"></i>Recent Orders</span>
                <a href="<?= BASE_URL ?>admin/pesanan/" class="btn btn-sm btn-primary">Lihat Semua</a></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>admin/pesanan/detail.php?id=<?= $r['id_pesanan'] ?>"><?= e($r['kode_pesanan']) ?></a></td>
                                <td><?= e($r['nama']) ?></td>
                                <td><?= tgl_waktu($r['tanggal']) ?></td>
                                <td><?= rupiah($r['total']) ?></td>
                                <td><?= status_badge($r['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span><i class="fas fa-fire me-2"></i>Produk Terlaris</span></div>
            <div class="card-body"><canvas id="topChart" height="220"></canvas></div>
            <?php if (!empty($topObat)): ?>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($topObat as $o): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= e($o['nama_obat']) ?>
                        <span class="badge bg-primary"><?= e($o['qty']) ?> terjual</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
            <div class="card-body pt-0">
                <div class="alert alert-secondary mb-0">Belum ada data produk terlaris.</div>
            </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-header"><span><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Stok Hampir Habis</span></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($lowStock as $o): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?= e($o['nama_obat']) ?></span>
                        <span class="badge bg-<?= $o['stok']<=10?'danger':'warning' ?>"><?= $o['stok'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$extra_js = '<script>
const sales = new Chart(document.getElementById("salesChart"), {
    type: "line",
    data: { labels: ' . json_encode($labels) . ', datasets: [{ label: "Pendapatan", data: ' . json_encode($dataPenjualan) . ', borderColor: "#2563eb", backgroundColor: "rgba(37,99,235,.15)", fill: true, tension: .35 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => "Rp " + (v/1000) + "k" } } } }
});
';
if (!empty($topObat)) {
    $extra_js .= 'const top = new Chart(document.getElementById("topChart"), {
    type: "bar",
    data: { labels: ' . json_encode($topLabels) . ', datasets: [{ label: "Terjual", data: ' . json_encode(array_map('intval', $topQty)) . ', backgroundColor: "#2563eb" }] },
    options: { indexAxis: "y", plugins: { legend: { display: false } } }
});
';
} else {
    $extra_js .= 'document.getElementById("topChart").style.display = "none";
';
}
$extra_js .= '</script>';
require_once '../includes/footer_admin.php';
