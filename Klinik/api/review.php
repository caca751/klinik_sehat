<?php
require_once '../config/koneksi.php';
require_login();
if (is_admin()) { echo json_encode(['status' => 'error', 'msg' => 'Akses ditolak']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$token = $input['csrf_token'] ?? $input['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
    echo json_encode(['status' => 'error', 'msg' => 'Token CSRF tidak valid']); exit;
}
$uid = $_SESSION['user']['id_user'];

if (($input['act'] ?? '') === 'add') {
    $id_obat = (int)($input['id_obat'] ?? 0);
    $rating = (int)($input['rating'] ?? 0);
    $komentar = clean($input['komentar'] ?? '');
    if ($rating < 1 || $rating > 5) { echo json_encode(['status' => 'error', 'msg' => 'Rating 1-5']); exit; }
    if (!$id_obat) { echo json_encode(['status' => 'error', 'msg' => 'Pilih obat']); exit; }

    $cek = $pdo->prepare("SELECT id_review FROM review WHERE id_user=? AND id_obat=?");
    $cek->execute([$uid, $id_obat]);
    if ($cek->fetch()) {
        $pdo->prepare("UPDATE review SET rating=?, komentar=?, tanggal=NOW() WHERE id_user=? AND id_obat=?")->execute([$rating, $komentar, $uid, $id_obat]);
        echo json_encode(['status' => 'ok', 'msg' => 'Review diperbarui']);
    } else {
        $pdo->prepare("INSERT INTO review (id_user, id_obat, rating, komentar, tanggal) VALUES (?,?,?,?,NOW())")->execute([$uid, $id_obat, $rating, $komentar]);
        echo json_encode(['status' => 'ok', 'msg' => 'Review ditambahkan']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak dikenal']);
}
