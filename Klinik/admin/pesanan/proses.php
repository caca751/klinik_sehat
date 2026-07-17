<?php
require_once '../../config/koneksi.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('error', 'Akses tidak valid.');
    redirect(BASE_URL . 'admin/pesanan/');
}

$id = (int)$_POST['id'];
$newStatus = $_POST['status'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id_pesanan=?");
$stmt->execute([$id]);
$pes = $stmt->fetch();
if (!$pes) { set_flash('error', 'Pesanan tidak ditemukan.'); redirect(BASE_URL . 'admin/pesanan/'); }

$old = $pes['status'];
$pdo->prepare("UPDATE pesanan SET status=? WHERE id_pesanan=?")->execute([$newStatus, $id]);

/* Notifikasi + restore stock saat dibatalkan */
if ($newStatus === 'Dibatalkan') {
    $items = $pdo->prepare("SELECT id_obat, jumlah FROM detail_pesanan WHERE id_pesanan=?");
    $items->execute([$id]);
    foreach ($items as $it) {
        $pdo->prepare("UPDATE obat SET stok = stok + ? WHERE id_obat=?")->execute([$it['jumlah'], $it['id_obat']]);
    }
    send_notif($pdo, $pes['id_user'], 'Pesanan Dibatalkan', "Pesanan {$pes['kode_pesanan']} dibatalkan.");
} elseif ($newStatus === 'Dikirim') {
    send_notif($pdo, $pes['id_user'], 'Pesanan Dikirim', "Pesanan {$pes['kode_pesanan']} sedang dikirim.");
} elseif ($newStatus === 'Selesai') {
    send_notif($pdo, $pes['id_user'], 'Pesanan Selesai', "Terima kasih, pesanan {$pes['kode_pesanan']} selesai.");
} elseif ($newStatus === 'Diproses') {
    send_notif($pdo, $pes['id_user'], 'Pesanan Diproses', "Pesanan {$pes['kode_pesanan']} sedang diproses.");
}

set_flash('success', "Status pesanan diubah menjadi $newStatus.");
redirect(BASE_URL . 'admin/pesanan/detail.php?id=' . $id);
