<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Kelola Review';
$active = 'review';

if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    $pdo->prepare("DELETE FROM review WHERE id_review=?")->execute([(int)$_GET['hapus']]);
    set_flash('success', 'Review dihapus.');
    redirect('');
}

$reviews = $pdo->query("SELECT r.*, u.nama, o.nama_obat FROM review r JOIN users u ON u.id_user=r.id_user JOIN obat o ON o.id_obat=r.id_obat ORDER BY r.tanggal DESC")->fetchAll();

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header"><span><i class="fas fa-star me-2"></i>Daftar Review Obat</span></div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Customer</th><th>Obat</th><th>Rating</th><th>Komentar</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['nama_obat']) ?></td>
                    <td><?= str_repeat('⭐', $r['rating']) ?></td>
                    <td><?= e($r['komentar']) ?></td>
                    <td><?= tgl_waktu($r['tanggal']) ?></td>
                    <td class="text-end">
                        <a href="?hapus=<?= $r['id_review'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="konfirmHapus(this.href,'Hapus review ini?');return false;"><i class="fas fa-trash"></i></a>
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
