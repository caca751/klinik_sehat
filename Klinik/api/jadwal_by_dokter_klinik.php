<?php
require_once '../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');
$id_dokter = (int)($_GET['id_dokter'] ?? 0);
$id_klinik = (int)($_GET['id_klinik'] ?? 0);
$rows = [];
if ($id_dokter && $id_klinik) {
    $stmt = $pdo->prepare("SELECT id_jadwal, hari, jam_mulai, jam_selesai, kuota FROM jadwal_praktik WHERE id_dokter=? AND id_klinik=? ORDER BY hari, jam_mulai");
    $stmt->execute([$id_dokter, $id_klinik]);
    $rows = $stmt->fetchAll();
}
echo json_encode($rows);
