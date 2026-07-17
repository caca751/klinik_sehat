<?php
require_once '../../config/koneksi.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('error', 'Akses tidak valid.');
    redirect(BASE_URL . 'admin/pengiriman/');
}

$id_pesanan = (int)($_POST['id_pesanan'] ?? 0);
$eks = clean($_POST['ekspedisi']);
$resi = clean($_POST['nomor_resi']);
$status = $_POST['status'] ?? '';
$pes = $pdo->prepare("SELECT kode_pesanan, id_user FROM pesanan WHERE id_pesanan=?");
$pes->execute([$id_pesanan]); $pes = $pes->fetch();
if (!$pes) { set_flash('error', 'Pesanan tidak ditemukan.'); redirect(BASE_URL . 'admin/pengiriman/'); }

$cek = $pdo->prepare("SELECT id_pengiriman FROM pengiriman WHERE id_pesanan=?");
$cek->execute([$id_pesanan]);
if ($cek->fetch()) {
    $pdo->prepare("UPDATE pengiriman SET ekspedisi=?, nomor_resi=?, status=? WHERE id_pesanan=?")->execute([$eks, $resi, $status, $id_pesanan]);
} else {
    $pdo->prepare("INSERT INTO pengiriman (id_pesanan, ekspedisi, nomor_resi, status) VALUES (?,?,?,?)")->execute([$id_pesanan, $eks, $resi, $status]);
}

if ($status === 'Diterima') {
    $pdo->prepare("UPDATE pesanan SET status='Selesai' WHERE id_pesanan=?")->execute([$id_pesanan]);
    send_notif($pdo, $pes['id_user'], 'Pesanan Diterima', "Pesanan {$pes['kode_pesanan']} telah diterima.");
} elseif ($status === 'Dikirim') {
    $pdo->prepare("UPDATE pesanan SET status='Dikirim' WHERE id_pesanan=?")->execute([$id_pesanan]);
    send_notif($pdo, $pes['id_user'], 'Pesanan Dikirim', "Pesanan {$pes['kode_pesanan']} sedang dikirim.");
}

set_flash('success', 'Data pengiriman disimpan.');
redirect(BASE_URL . 'admin/pesanan/detail.php?id=' . $id_pesanan);
