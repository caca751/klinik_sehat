<?php
require_once '../config/koneksi.php';
require_login();
if (is_admin()) { echo json_encode(['status' => 'error', 'msg' => 'Akses ditolak']); exit; }

$q = clean($_GET['q'] ?? '');
$kat = (int)($_GET['kategori'] ?? 0);
$hmin = isset($_GET['harga_min']) && $_GET['harga_min'] !== '' ? (float)$_GET['harga_min'] : 0;
$hmax = isset($_GET['harga_max']) && $_GET['harga_max'] !== '' ? (float)$_GET['harga_max'] : 0;
$id_apotek = isset($_GET['id_apotek']) && $_GET['id_apotek'] !== '' ? (int)$_GET['id_apotek'] : 0;

$sql = "SELECT o.*, k.nama_kategori FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori";
$params = [];
if ($id_apotek > 0) {
    $sql .= " LEFT JOIN harga_stok_apotek hsa ON hsa.id_obat = o.id_obat AND hsa.id_apotek = ?";
    $params[] = $id_apotek;
}
$sql .= " WHERE 1";
if ($q) { $sql .= " AND o.nama_obat LIKE ?"; $params[] = "%$q%"; }
if ($kat) { $sql .= " AND o.id_kategori = ?"; $params[] = $kat; }
if ($hmin > 0) {
    if ($id_apotek > 0) {
        $sql .= " AND hsa.harga >= ?";
    } else {
        $sql .= " AND o.harga >= ?";
    }
    $params[] = $hmin;
}
if ($hmax > 0) {
    if ($id_apotek > 0) {
        $sql .= " AND hsa.harga <= ?";
    } else {
        $sql .= " AND o.harga <= ?";
    }
    $params[] = $hmax;
}
$sql .= " ORDER BY o.nama_obat LIMIT 60";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$html = '';
if (empty($rows)) {
    $html = '<div class="empty-state"><i class="fas fa-box-open"></i><p>Obat tidak ditemukan.</p></div>';
} else {
    foreach ($rows as $o) {
        $img = $o['gambar'] ? URL_OBAT . e($o['gambar']) : BASE_URL . 'assets/images/no-image.svg';
        
        $hargaTampil = rupiah($o['harga']);
        $stokTampil = $o['stok'];
        if ($id_apotek > 0) {
            $hs = $pdo->prepare("SELECT harga, stok FROM harga_stok_apotek WHERE id_obat=? AND id_apotek=?");
            $hs->execute([$o['id_obat'], $id_apotek]);
            $hr = $hs->fetch();
            if ($hr) {
                $hargaTampil = rupiah($hr['harga']);
                $stokTampil = (int)$hr['stok'];
            } else {
                $stokTampil = 0;
            }
        }
        
        $btn = $stokTampil > 0
            ? '<button class="btn btn-sm btn-primary" onclick="addToCart(' . $o['id_obat'] . ', ' . $id_apotek . ')"><i class="fas fa-cart-plus"></i></button>'
            : '<span class="btn btn-sm btn-secondary disabled">Habis</span>';
        
        $html .= '<div class="product-card">
            <div class="img"><img src="' . $img . '" onerror="this.src=\'' . BASE_URL . 'assets/images/no-image.svg\'" alt=""></div>
            <div class="body">
                <div class="name">' . e($o['nama_obat']) . '</div>
                <div class="cat">' . e($o['nama_kategori']) . '</div>
                <div class="price">' . $hargaTampil . '</div>
                <div class="foot">
                    <a href="' . BASE_URL . 'user/obat/detail.php?id=' . $o['id_obat'] . ($id_apotek > 0 ? '&apotek=' . $id_apotek : '') . '" class="btn btn-sm btn-outline-primary flex-grow-1">Detail</a>
                    ' . $btn . '
                </div>
            </div>
        </div>';
    }
}
echo json_encode(['status' => 'ok', 'html' => $html]);
