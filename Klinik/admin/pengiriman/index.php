<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Kelola Pengiriman';
$active = 'pengiriman';

$ships = $pdo->query("SELECT pg.*, p.kode_pesanan, u.nama FROM pengiriman pg JOIN pesanan p ON p.id_pesanan=pg.id_pesanan JOIN users u ON u.id_user=p.id_user ORDER BY pg.id_pengiriman DESC")->fetchAll();

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header"><span><i class="fas fa-box me-2"></i>Daftar Pengiriman</span></div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Kode Pesanan</th><th>Customer</th><th>Ekspedisi</th><th>No. Resi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($ships as $s): ?>
                <tr>
                    <td><?= e($s['kode_pesanan']) ?></td>
                    <td><?= e($s['nama']) ?></td>
                    <td><?= e($s['ekspedisi'] ?? '-') ?></td>
                    <td><?= e($s['nomor_resi'] ?? '-') ?></td>
                    <td><?= status_badge($s['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>admin/pengiriman/edit.php?id=<?= $s['id_pengiriman'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
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
