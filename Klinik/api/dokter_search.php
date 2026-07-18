<?php
require_once '../config/koneksi.php';
header('Content-Type: text/html; charset=utf-8');

$nama = clean($_GET['nama'] ?? '');
$id_spesialis = (int)($_GET['id_spesialis'] ?? 0);
$id_kota = (int)($_GET['id_kota'] ?? 0);
$id_dokter = (int)($_GET['id_dokter'] ?? 0);

$sql = "SELECT d.*, s.nama_spesialis, GROUP_CONCAT(DISTINCT kt.nama_kota SEPARATOR ', ') as kota_list,
        GROUP_CONCAT(DISTINCT kt.id_kota SEPARATOR ',') as kota_ids,
        GROUP_CONCAT(DISTINCT k.nama_klinik SEPARATOR ', ') as klinik_list,
        GROUP_CONCAT(DISTINCT k.id_klinik SEPARATOR ',') as klinik_ids,
        COUNT(DISTINCT j.id_jadwal) as jumlah_jadwal
        FROM dokter d
        LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis
        LEFT JOIN dokter_klinik dk ON dk.id_dokter=d.id_dokter
        LEFT JOIN klinik k ON k.id_klinik=dk.id_klinik
        LEFT JOIN kota kt ON kt.id_kota=k.id_kota
        LEFT JOIN jadwal_praktik j ON j.id_dokter=d.id_dokter
        WHERE 1=1";
$params = [];
if ($id_dokter) { $sql .= " AND d.id_dokter=?"; $params[] = $id_dokter; }
if ($nama) { $sql .= " AND d.nama_dokter LIKE ?"; $params[] = "%$nama%"; }
if ($id_spesialis) { $sql .= " AND d.id_spesialis=?"; $params[] = $id_spesialis; }
if ($id_kota) { $sql .= " AND kt.id_kota=?"; $params[] = $id_kota; }
$sql .= " GROUP BY d.id_dokter ORDER BY d.nama_dokter";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

if (empty($rows)) { echo '<p class="text-muted">Tidak ada dokter ditemukan.</p>'; exit; }
?>
<div class="row">
<?php foreach ($rows as $r): ?>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="avatar rounded-circle overflow-hidden" style="width:60px;height:60px;">
    <img src="<?= !empty($r['foto']) ? URL_DOKTER . e($r['foto']) : BASE_URL . 'assets/images/no-image.svg'; ?>"
         alt="<?= e($r['nama_dokter']) ?>"
         style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div>
                        <h6 class="mb-0"><?= e($r['nama_dokter']) ?></h6>
                        <small class="text-muted"><?= e($r['nama_spesialis']) ?></small>
                    </div>
                </div>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-1"></i> <?= e($r['kota_list'] ?? '-') ?></p>
                <p class="mb-1"><i class="fas fa-hospital me-1"></i> <?= e($r['klinik_list'] ?? '-') ?></p>
                <p class="mb-1"><i class="fas fa-calendar me-1"></i> <?= e($r['jumlah_jadwal'] ?? 0) ?> jadwal</p>
                <p class="mb-0 text-primary fw-semibold"><?= rupiah($r['biaya_konsultasi']) ?> / konsultasi</p>
                <div class="mt-3 text-end">
                    <?php
                        $bookingUrl = BASE_URL . 'user/booking/buat.php?id_dokter=' . e($r['id_dokter']);
                        if (!empty($r['id_spesialis'])) {
                            $bookingUrl .= '&id_spesialis=' . e($r['id_spesialis']);
                        }
                        if (!empty($r['kota_ids'])) {
                            $kotaIds = explode(',', $r['kota_ids']);
                            if (!empty($kotaIds[0])) {
                                $bookingUrl .= '&id_kota=' . e($kotaIds[0]);
                            }
                        }
                        if (!empty($r['klinik_ids'])) {
                            $klinikIds = explode(',', $r['klinik_ids']);
                            if (!empty($klinikIds[0])) {
                                $bookingUrl .= '&id_klinik=' . e($klinikIds[0]);
                            }
                        }
                    ?>
                    <a href="<?= $bookingUrl ?>" class="btn btn-sm btn-primary">Booking</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
