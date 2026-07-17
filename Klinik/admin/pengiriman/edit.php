<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Edit Pengiriman';
$active = 'pengiriman';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT pg.*, p.kode_pesanan, u.nama FROM pengiriman pg JOIN pesanan p ON p.id_pesanan=pg.id_pesanan JOIN users u ON u.id_user=p.id_user WHERE pg.id_pengiriman=?");
$stmt->execute([$id]);
$ship = $stmt->fetch();
if (!$ship) { set_flash('error', 'Data tidak ditemukan.'); redirect(BASE_URL . 'admin/pengiriman/'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    $eks = clean($_POST['ekspedisi']);
    $resi = clean($_POST['nomor_resi']);
    $status = $_POST['status'] ?? '';
    $pdo->prepare("UPDATE pengiriman SET ekspedisi=?, nomor_resi=?, status=? WHERE id_pengiriman=?")->execute([$eks, $resi, $status, $id]);
    if ($status === 'Diterima') {
        $pdo->prepare("UPDATE pesanan SET status='Selesai' WHERE id_pesanan=?")->execute([$ship['id_pesanan']]);
        send_notif($pdo, $ship['id_user'], 'Pesanan Diterima', "Pesanan {$ship['kode_pesanan']} telah diterima.");
    } elseif ($status === 'Dikirim') {
        $pdo->prepare("UPDATE pesanan SET status='Dikirim' WHERE id_pesanan=?")->execute([$ship['id_pesanan']]);
    }
    set_flash('success', 'Pengiriman diupdate.');
    redirect(BASE_URL . 'admin/pengiriman/');
}

require_once '../../includes/header_admin.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Edit Pengiriman - <?= e($ship['kode_pesanan']) ?></div>
            <div class="card-body">
                <p>Customer: <strong><?= e($ship['nama']) ?></strong></p>
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Ekspedisi</label>
                        <select name="ekspedisi" class="form-select">
                            <?php foreach (['JNE','J&T','Sicepat','Pos Indonesia','Ninja Express'] as $e): ?>
                                <option <?= $ship['ekspedisi']===$e?'selected':'' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">No. Resi</label><input type="text" name="nomor_resi" class="form-control" value="<?= e($ship['nomor_resi']) ?>"></div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['Dikemas','Dikirim','Diterima','Gagal'] as $s): ?>
                                <option value="<?= $s ?>" <?= $ship['status']===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <button class="btn btn-primary">Simpan</button>
                    <a href="<?= BASE_URL ?>admin/pengiriman/" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>
