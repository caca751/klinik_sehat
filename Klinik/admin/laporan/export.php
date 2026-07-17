<?php
require_once '../../config/koneksi.php';
require_admin();

$r = $_GET['r'] ?? 'penjualan';
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

$filename = 'laporan_' . $r . '_' . date('Ymd') . '.csv';

if ($r === 'penjualan') {
    $stmt = $pdo->prepare("SELECT p.kode_pesanan, u.nama, p.tanggal, p.total, p.status FROM pesanan p JOIN users u ON u.id_user=p.id_user WHERE DATE(p.tanggal) BETWEEN ? AND ? ORDER BY p.tanggal DESC");
    $stmt->execute([$from, $to]);
    $header = ['Kode Pesanan', 'Customer', 'Tanggal', 'Total', 'Status'];
} elseif ($r === 'pendapatan') {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%Y-%m') bln, COUNT(*) jml, SUM(total) tot FROM pesanan WHERE status IN ('Diproses','Dikirim','Selesai') AND DATE(tanggal) BETWEEN ? AND ? GROUP BY bln ORDER BY bln");
    $stmt->execute([$from, $to]);
    $header = ['Bulan', 'Jumlah Pesanan', 'Pendapatan'];
} elseif ($r === 'stok') {
    $stmt = $pdo->query("SELECT o.kode_obat, o.nama_obat, h.harga, h.stok FROM harga_stok_apotek h JOIN obat o ON o.id_obat=h.id_obat ORDER BY h.stok ASC");
    $header = ['Kode Obat', 'Nama Obat', 'Harga', 'Stok'];
} elseif ($r === 'user') {
    $stmt = $pdo->query("SELECT nama, email, no_hp, alamat, created_at FROM users WHERE role='customer' ORDER BY id_user");
    $header = ['Nama', 'Email', 'No. HP', 'Alamat', 'Bergabung'];
} else {
    $stmt = $pdo->prepare("SELECT o.kode_obat, o.nama_obat, k.nama_kategori, SUM(d.jumlah) terjual, SUM(d.subtotal) pendapatan FROM detail_pesanan d JOIN obat o ON o.id_obat=d.id_obat LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori GROUP BY d.id_obat ORDER BY terjual DESC");
    $stmt->execute();
    $header = ['Kode Obat', 'Nama Obat', 'Kategori', 'Terjual', 'Pendapatan'];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
fputcsv($out, $header);
while ($row = $stmt->fetch()) {
    fputcsv($out, array_values($row));
}
fclose($out);
exit;
