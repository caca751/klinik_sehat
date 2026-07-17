<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Kelola Pembayaran';
$active = 'pembayaran';

$pays = $pdo->query("SELECT pb.*, p.kode_pesanan, p.total, u.nama, mp.nama_metode AS nama_metode, mp.nomor AS metode_nomor, mp.pemilik AS metode_pemilik, mp.tipe AS metode_tipe FROM pembayaran pb LEFT JOIN metode_pembayaran mp ON mp.id_metode = pb.id_metode JOIN pesanan p ON p.id_pesanan=pb.id_pesanan JOIN users u ON u.id_user=p.id_user ORDER BY pb.id_pembayaran DESC")->fetchAll();

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header"><span><i class="fas fa-money-bill-wave me-2"></i>Daftar Pembayaran</span></div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Kode Pesanan</th><th>Customer</th><th>Metode</th><th>Tujuan</th><th>Bukti</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($pays as $pb): ?>
                <tr>
                    <td><?= e($pb['kode_pesanan']) ?></td>
                    <td><?= e($pb['nama']) ?></td>
                    <td><?= e($pb['nama_metode'] ?? '-') ?></td>
                    <td><?php if (!empty($pb['id_metode']) && !empty($pb['metode_nomor'])): ?><?= e($pb['metode_nomor']) ?> a.n. <?= e($pb['metode_pemilik']) ?><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                    <td><?php if ($pb['bukti_transfer']): ?><a href="<?= URL_BUKTI . e($pb['bukti_transfer']) ?>" target="_blank"><img src="<?= URL_BUKTI . e($pb['bukti_transfer']) ?>" class="img-thumb" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'"></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                    <td><?= tgl_waktu($pb['tanggal_bayar']) ?></td>
                    <td><?= status_badge($pb['status']) ?></td>
                    <td class="text-end">
                        <?php if ($pb['status'] !== 'Lunas'): ?>
                        <a href="<?= BASE_URL ?>admin/pembayaran/proses.php?id=<?= $pb['id_pembayaran'] ?>&st=Lunas&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-success" onclick="return confirm('Verifikasi pembayaran ini sebagai Lunas?')"><i class="fas fa-check"></i> Verifikasi</a>
                        <a href="<?= BASE_URL ?>admin/pembayaran/proses.php?id=<?= $pb['id_pembayaran'] ?>&st=Ditolak&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pembayaran ini?')"><i class="fas fa-xmark"></i></a>
                        <?php else: ?><span class="text-success"><i class="fas fa-check-circle"></i> Terverifikasi</span><?php endif; ?>
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
