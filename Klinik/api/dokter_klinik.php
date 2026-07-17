<?php
require_once '../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');
$id_dokter = (int)($_GET['id_dokter'] ?? 0);
$rows = $pdo->prepare("SELECT k.id_klinik, k.nama_klinik, kt.nama_kota FROM dokter_klinik dk JOIN klinik k ON k.id_klinik=dk.id_klinik JOIN kota kt ON kt.id_kota=k.id_kota WHERE dk.id_dokter=? ORDER BY k.nama_klinik");
$rows->execute([$id_dokter]);
echo json_encode($rows->fetchAll());
