<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Riwayat Pesanan';
$active = 'pesanan';
$uid = $_SESSION['user']['id_user'];

$pesanan = $pdo->prepare("SELECT * FROM pesanan WHERE id_user=? ORDER BY tanggal DESC");
$pesanan->execute([$uid]);
$pesanan_list = $pesanan->fetchAll();

require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-receipt me-2"></i>Riwayat Pesanan</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" id="table">
                <thead><tr><th>Kode</th><th>Tanggal</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($pesanan_list)): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada pesanan</td></tr><?php endif; ?>
                <?php foreach ($pesanan_list as $p): ?>
                    <tr>
                        <td><?= e($p['kode_pesanan']) ?></td>
                        <td><?= tgl_waktu($p['tanggal']) ?></td>
                        <td><?= rupiah($p['total']) ?></td>
                        <td><?= status_badge($p['status']) ?></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>user/pesanan/detail.php?id=<?= $p['id_pesanan'] ?>" class="btn btn-sm btn-primary">Detail</a>
                            <a href="<?= BASE_URL ?>user/tracking/?kode=<?= e($p['kode_pesanan']) ?>" class="btn btn-sm btn-outline-info">Tracking</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$extra_js = '<script>if(window.$){$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});}</script>';
require_once '../../includes/footer_user.php';
