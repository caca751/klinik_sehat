<?php
require_once '../config/koneksi.php';
require_login();
if (is_admin()) { echo json_encode(['status' => 'error', 'msg' => 'Akses ditolak']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$token = $input['csrf_token'] ?? $input['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
    echo json_encode(['status' => 'error', 'msg' => 'Token CSRF tidak valid']);
    exit;
}
$uid = $_SESSION['user']['id_user'];
$act = $input['act'] ?? '';

if ($act === 'add') {
    $id_obat = (int)($input['id_obat'] ?? 0);
    $id_apotek = isset($input['id_apotek']) ? (int)$input['id_apotek'] : 0;
    $obat = $pdo->prepare("SELECT id_obat, stok, nama_obat FROM obat WHERE id_obat=?");
    $obat->execute([$id_obat]); $obat = $obat->fetch();
    if (!$obat) { echo json_encode(['status' => 'error', 'msg' => 'Obat tidak ditemukan']); exit; }

    $stokApotek = 0;
    if ($id_apotek > 0) {
        $hs = $pdo->prepare("SELECT stok FROM harga_stok_apotek WHERE id_obat=? AND id_apotek=?");
        $hs->execute([$id_obat, $id_apotek]);
        $hr = $hs->fetch();
        if ($hr) { $stokApotek = (int)$hr['stok']; }
    }
    $globalStok = (int)$obat['stok'];
    $stokMaks = $id_apotek > 0 ? min($stokApotek, $globalStok) : $globalStok;
    
    if ($stokMaks <= 0) { echo json_encode(['status' => 'error', 'msg' => 'Stok habis di apotek ini']); exit; }

    if ($id_apotek > 0) {
        $cek = $pdo->prepare("SELECT id_keranjang, jumlah FROM keranjang WHERE id_user=? AND id_obat=? AND id_apotek=?");
        $cek->execute([$uid, $id_obat, $id_apotek]);
        $row = $cek->fetch();
        if ($row) {
            $new = min($row['jumlah'] + 1, $stokMaks);
            $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id_keranjang=?")->execute([$new, $row['id_keranjang']]);
        } else {
            $pdo->prepare("INSERT INTO keranjang (id_user, id_obat, id_apotek, jumlah) VALUES (?,?,?,1)")->execute([$uid, $id_obat, $id_apotek]);
        }
    } else {
        $cek = $pdo->prepare("SELECT id_keranjang, jumlah FROM keranjang WHERE id_user=? AND id_obat=? AND (id_apotek IS NULL OR id_apotek=0)");
        $cek->execute([$uid, $id_obat]);
        $row = $cek->fetch();
        if ($row) {
            $new = min($row['jumlah'] + 1, $globalStok);
            $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id_keranjang=?")->execute([$new, $row['id_keranjang']]);
        } else {
            $pdo->prepare("INSERT INTO keranjang (id_user, id_obat, id_apotek, jumlah) VALUES (?,?,NULL,1)")->execute([$uid, $id_obat]);
        }
    }
    echo json_encode(['status' => 'ok', 'msg' => 'Ditambahkan ke keranjang', 'count' => cart_count($pdo, $uid)]);
}
elseif ($act === 'update') {
    $id_keranjang = (int)($input['id_keranjang'] ?? 0);
    $jumlah = max(1, (int)($input['jumlah'] ?? 1));
    $cek = $pdo->prepare("SELECT k.*, o.stok FROM keranjang k JOIN obat o ON o.id_obat=k.id_obat WHERE k.id_keranjang=? AND k.id_user=?");
    $cek->execute([$id_keranjang, $uid]); $row = $cek->fetch();
    if (!$row) { echo json_encode(['status' => 'error', 'msg' => 'Item tidak ditemukan']); exit; }
    $stokMaks = (int)$row['stok'];
    if ($row['id_apotek'] > 0) {
        $hs = $pdo->prepare("SELECT stok FROM harga_stok_apotek WHERE id_obat=? AND id_apotek=?");
        $hs->execute([$row['id_obat'], $row['id_apotek']]);
        $hr = $hs->fetch();
        if ($hr) { $stokMaks = min($stokMaks, (int)$hr['stok']); }
    }
    $jumlah = min($jumlah, $stokMaks);
    $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id_keranjang=?")->execute([$jumlah, $id_keranjang]);
    echo json_encode(['status' => 'ok', 'msg' => 'Diperbarui', 'count' => cart_count($pdo, $uid)]);
}
elseif ($act === 'remove') {
    $id_keranjang = (int)($input['id_keranjang'] ?? 0);
    $pdo->prepare("DELETE FROM keranjang WHERE id_keranjang=? AND id_user=?")->execute([$id_keranjang, $uid]);
    echo json_encode(['status' => 'ok', 'msg' => 'Dihapus', 'count' => cart_count($pdo, $uid)]);
}
else {
    echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak dikenal']);
}
