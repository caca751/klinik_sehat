<?php
require_once '../../config/koneksi.php';
require_admin();
if (!csrf_get_valid()) { set_flash('error', 'Token tidak valid.'); redirect(BASE_URL . 'admin/pembayaran/'); }

$id = (int)$_GET['id'];
$st = $_GET['st'] === 'Ditolak' ? 'Ditolak' : 'Lunas';
$stmt = $pdo->prepare("SELECT pb.*, p.kode_pesanan, p.id_user FROM pembayaran pb JOIN pesanan p ON p.id_pesanan=pb.id_pesanan WHERE pb.id_pembayaran=?");
$stmt->execute([$id]);
$pb = $stmt->fetch();
if ($pb) {
    $pdo->prepare("UPDATE pembayaran SET status=? WHERE id_pembayaran=?")->execute([$st, $id]);
    if ($st === 'Lunas') {
        $pdo->prepare("UPDATE pesanan SET status='Diproses' WHERE id_pesanan=?")->execute([$pb['id_pesanan']]);
        send_notif($pdo, $pb['id_user'], 'Pembayaran Lunas', "Pembayaran pesanan {$pb['kode_pesanan']} telah diverifikasi.");
    } else {
        send_notif($pdo, $pb['id_user'], 'Pembayaran Ditolak', "Pembayaran pesanan {$pb['kode_pesanan']} ditolak.");
    }
    set_flash('success', 'Status pembayaran diupdate.');
} else {
    set_flash('error', 'Data tidak ditemukan.');
}
redirect(BASE_URL . 'admin/pembayaran/');
