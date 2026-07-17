<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Kelola Pesanan';
$active = 'pesanan';

$filter = clean($_GET['status'] ?? '');
$pesanan = [];
if ($filter) {
    $stmt = $pdo->prepare("SELECT p.*, u.nama FROM pesanan p JOIN users u ON u.id_user=p.id_user WHERE p.status = ? ORDER BY p.tanggal DESC");
    $stmt->execute([$filter]);
    $pesanan = $stmt->fetchAll();
} else {
    $pesanan = $pdo->query("SELECT p.*, u.nama FROM pesanan p JOIN users u ON u.id_user=p.id_user ORDER BY p.tanggal DESC")->fetchAll();
}

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-shopping-cart me-2"></i>Daftar Pesanan</span>
        <div>
            <a href="?status=" class="btn btn-sm btn-outline-secondary <?= $filter==''?'active':'' ?>">Semua</a>
            <a href="?status=Menunggu%20Pembayaran" class="btn btn-sm btn-outline-warning <?= $filter=='Menunggu Pembayaran'?'active':'' ?>">Menunggu</a>
            <a href="?status=Diproses" class="btn btn-sm btn-outline-info <?= $filter=='Diproses'?'active':'' ?>">Diproses</a>
            <a href="?status=Dikirim" class="btn btn-sm btn-outline-primary <?= $filter=='Dikirim'?'active':'' ?>">Dikirim</a>
            <a href="?status=Selesai" class="btn btn-sm btn-outline-success <?= $filter=='Selesai'?'active':'' ?>">Selesai</a>
            <a href="?status=Dibatalkan" class="btn btn-sm btn-outline-secondary <?= $filter=='Dibatalkan'?'active':'' ?>">Batal</a>
        </div>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Kode</th><th>Customer</th><th>Tanggal</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($pesanan as $p): ?>
                <tr>
                    <td><?= e($p['kode_pesanan']) ?></td>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= tgl_waktu($p['tanggal']) ?></td>
                    <td><?= rupiah($p['total']) ?></td>
                    <td><?= status_badge($p['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>admin/pesanan/detail.php?id=<?= $p['id_pesanan'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$extra_js = '<script>$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});</script>';
require_once '../../includes/footer_admin.php';
