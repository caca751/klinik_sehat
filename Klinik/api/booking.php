<?php
require_once '../config/koneksi.php';
require_login();
header('Content-Type: application/json; charset=utf-8');
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Method tidak diizinkan']);
    exit;
}
if (!csrf_valid()) {
    echo json_encode(['success' => false, 'msg' => 'Token CSRF tidak valid']);
    exit;
}

$id_dokter = (int)($_POST['id_dokter'] ?? 0);
$id_klinik = (int)($_POST['id_klinik'] ?? 0);
$id_jadwal = !empty($_POST['id_jadwal']) ? (int)$_POST['id_jadwal'] : null;
$tanggal = clean($_POST['tanggal_booking'] ?? '');
$keluhan = clean($_POST['keluhan'] ?? '');

if (!$id_dokter || !$id_klinik || !$tanggal) {
    echo json_encode(['success' => false, 'msg' => 'Data tidak lengkap']);
    exit;
}

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dokter_klinik WHERE id_dokter=? AND id_klinik=?");
    $stmt->execute([$id_dokter, $id_klinik]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'msg' => 'Klinik tidak tersedia untuk dokter yang dipilih']);
        exit;
    }

    if ($id_jadwal) {
        $stmt = $pdo->prepare("SELECT kuota, hari, id_dokter, id_klinik FROM jadwal_praktik WHERE id_jadwal=?");
        $stmt->execute([$id_jadwal]);
        $jadwal = $stmt->fetch();
        if ($jadwal) {
            if ((int)$jadwal['id_dokter'] !== $id_dokter || (int)$jadwal['id_klinik'] !== $id_klinik) {
                echo json_encode(['success' => false, 'msg' => 'Jadwal praktik tidak sesuai dengan dokter dan klinik yang dipilih']);
                exit;
            }
            $hari_map = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
            $hari_tanggal = $hari_map[date('l', strtotime($tanggal))];
            if ($jadwal['hari'] !== $hari_tanggal) {
                echo json_encode(['success' => false, 'msg' => 'Jadwal tersedia hari ' . $jadwal['hari'] . ', tetapi tanggal yang dipilih adalah hari ' . $hari_tanggal]);
                exit;
            }
            $aktif = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE id_jadwal=? AND tanggal_booking=? AND status IN ('Menunggu','Selesai')");
            $aktif->execute([$id_jadwal, $tanggal]);
            if ($aktif->fetchColumn() >= $jadwal['kuota']) {
                echo json_encode(['success' => false, 'msg' => 'Kuota jadwal sudah penuh untuk tanggal tersebut']);
                exit;
            }
        }
    }

$kode = 'BKG-' . date('Ymd') . '-' . str_pad((int)$pdo->query("SELECT COUNT(*) FROM booking WHERE DATE(created_at)=CURDATE()")->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);

$stmt = $pdo->prepare("INSERT INTO booking (kode_booking, id_user, id_dokter, id_klinik, id_jadwal, tanggal_booking, keluhan, status, created_at) VALUES (?,?,?,?,?,?,?,'Menunggu',NOW())");
if ($stmt->execute([$kode, $user['id_user'], $id_dokter, $id_klinik, $id_jadwal, $tanggal, $keluhan])) {
    $id = $pdo->lastInsertId();
    send_notif($pdo, $user['id_user'], 'Booking Diterima', "Booking $kode berhasil dibuat.");
    echo json_encode(['success' => true, 'msg' => 'Booking berhasil dibuat', 'kode' => $kode, 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Gagal membuat booking']);
}
