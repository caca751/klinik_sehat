<?php
if (!defined('BASE_URL')) {
    require_once 'config/koneksi.php';
}
$page_title = 'Beranda';
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();

$jadwalRows = $pdo->query("SELECT d.id_dokter, d.nama_dokter, d.gender, d.no_hp, d.email, d.biaya_konsultasi, d.foto,
    s.nama_spesialis,
    k.id_klinik, k.nama_klinik, k.alamat as klinik_alamat, kt.id_kota, kt.nama_kota,
    j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai, j.kuota
    FROM jadwal_praktik j
    LEFT JOIN dokter d ON d.id_dokter=j.id_dokter
    LEFT JOIN spesialis s ON s.id_spesialis=d.id_spesialis
    LEFT JOIN klinik k ON k.id_klinik=j.id_klinik
    LEFT JOIN kota kt ON kt.id_kota=k.id_kota
    ORDER BY d.nama_dokter, kt.nama_kota, j.hari, j.jam_mulai")->fetchAll();

$dokterMap = [];
foreach ($jadwalRows as $r) {
    $id = (int)$r['id_dokter'];
    if (!isset($dokterMap[$id])) {
        $dokterMap[$id] = [
            'nama_dokter' => $r['nama_dokter'],
            'nama_spesialis' => $r['nama_spesialis'],
            'biaya_konsultasi' => $r['biaya_konsultasi'],
            'no_hp' => $r['no_hp'],
            'email' => $r['email'],
            'foto' => $r['foto'],
            'kota_list' => [],
            'klinik_list' => [],
            'jadwal_count' => 0,
            'schedules' => []
        ];
    }
    $dokterMap[$id]['kota_list'][$r['id_kota']] = $r['nama_kota'];
    $dokterMap[$id]['klinik_list'][$r['id_klinik']] = $r['nama_klinik'];
    $dokterMap[$id]['jadwal_count']++;
    $dokterMap[$id]['schedules'][] = [
        'nama_klinik' => $r['nama_klinik'],
        'nama_kota' => $r['nama_kota'],
        'id_kota' => $r['id_kota'],
        'alamat' => $r['klinik_alamat'],
        'hari' => $r['hari'],
        'jam_mulai' => substr($r['jam_mulai'],0,5),
        'jam_selesai' => substr($r['jam_selesai'],0,5),
        'kuota' => $r['kuota']
    ];
}

$kliniks = $pdo->query("SELECT k.*, kt.nama_kota FROM klinik k LEFT JOIN kota kt ON kt.id_kota=k.id_kota ORDER BY k.nama_klinik")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | Klinik Sehat</title>
    <link href="<?= BASE_URL ?>assets/images/logo.svg?v=2" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        body { font-size: 15px; line-height: 1.6; }
        .navbar-brand { font-size: 1.25rem; }
        .nav-link { font-size: 0.95rem; font-weight: 500; }
        .hero-title { font-size: 2.75rem; font-weight: 800; line-height: 1.2; }
        .hero-desc { font-size: 1.1rem; line-height: 1.7; opacity: 0.95; }
        .section-title { font-size: 1.75rem; font-weight: 700; }
        .section-subtitle { font-size: 1rem; color: var(--text-muted); }
        .dokter-nama { font-size: 1.05rem; }
        .dokter-info { font-size: 0.92rem; line-height: 1.6; }
        .klinik-nama { font-size: 1.05rem; }
        .klinik-info { font-size: 0.92rem; line-height: 1.6; }
        .footer-title { font-size: 1.05rem; }
        .footer-text { font-size: 0.92rem; line-height: 1.7; opacity: 0.85; }
        .footer-city { font-size: 0.95rem; cursor: pointer; transition: all 0.2s; padding: 4px 8px; border-radius: 6px; }
        .footer-city:hover { background: rgba(255,255,255,0.1); }
        .footer-city.active { background: rgba(255,255,255,0.2); font-weight: 600; }
        .card-doctor, .card-clinic { transition: all 0.3s ease; }
        .card-doctor.hidden-card, .card-clinic.hidden-card { display: none; }
        .filter-label { font-weight: 600; color: var(--primary); }
        .schedule-item { border-left: 3px solid var(--primary); padding-left: 10px; margin-bottom: 8px; }
        .schedule-klinik { font-weight: 600; color: var(--text); }
        .schedule-detail { font-size: 0.9rem; color: var(--text-muted); }
        .schedule-detail i { width: 14px; text-align: center; margin-right: 4px; }
        .badge-hari { font-size: 0.8rem; font-weight: 600; }
    </style>
    <script>window.APP_BASE_URL='<?= BASE_URL ?>';window.APP_DEFAULT_THEME='<?= ($_SESSION['dark_mode'] ?? 'light') ?>';window.CSRF='<?= csrf_token() ?>';</script>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="36"> Klinik Sehat
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navpublic">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navpublic">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#dokter">Dokter</a></li>
                <li class="nav-item"><a class="nav-link" href="#klinik">Klinik</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-primary btn-sm">Login</a>
                <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-primary btn-sm">Daftar</a>
            </div>
        </div>
    </div>
</nav>

<section id="beranda" class="py-5" style="background:linear-gradient(135deg, #16587B 0%, #84B3CE 100%);color:#fff;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title mb-3" style="color:#fff;">Kesehatan Anda, Prioritas Kami</h1>
                <p class="hero-desc mb-4" style="color:rgba(255,255,255,.9);">Akses layanan konsultasi dokter umum dan spesialis, booking online, dan pemesanan obat dengan mudah di <strong style="color:#F5EEDD;">Klinik Sehat</strong>. Tersedia di berbagai kota besar Indonesia.</p>
                <div class="d-flex gap-2">
                    <a href="#dokter" class="btn btn-light btn-lg fw-semibold">Cari Dokter</a>
                    <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-outline-light btn-lg fw-semibold">Daftar Sekarang</a>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <i class="fas fa-user-md" style="font-size:200px;opacity:.85;color:rgba(255,255,255,.9);"></i>
            </div>
        </div>
    </div>
</section>

<section id="dokter" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title fw-bold">Dokter Spesialis Kami</h2>
            <p class="section-subtitle">Temukan dokter sesuai kebutuhan dengan spesialisasi dan lokasi praktiknya.</p>
        </div>
        <div id="dokterFilter" class="text-center mb-4" style="display:none;">
            <span class="filter-label"><i class="fas fa-filter me-1"></i> Filter: </span>
            <span id="filterKotaDokter" class="badge bg-primary"></span>
            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="resetFilter()">Reset</button>
        </div>
        <div class="row g-4" id="dokterList">
            <?php foreach ($dokterMap as $d): 
                $kotaList = array_values($d['kota_list']);
                $kotaStr = implode(', ', $kotaList);
            ?>
            <div class="col-md-6 col-lg-4 card-doctor" data-kota="<?= strtolower(e($kotaStr)) ?>">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar rounded-circle overflow-hidden d-flex align-items-center justify-content-center"
     style="width:60px;height:60px;flex-shrink:0;">
    <img src="<?= !empty($d['foto']) ? URL_DOKTER . e($d['foto']) : BASE_URL . 'assets/images/no-image.svg'; ?>"
         alt="<?= e($d['nama_dokter']) ?>"
         style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div>
                                <h6 class="mb-0 dokter-nama fw-bold"><?= e($d['nama_dokter']) ?></h6>
                                <small class="text-muted"><?= e($d['nama_spesialis']) ?></small>
                            </div>
                        </div>
                        <div class="dokter-info">
                            <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <strong>Lokasi:</strong> <?= e($kotaStr ?: '-') ?></p>
                            <?php foreach ($d['schedules'] as $j): ?>
                            <div class="schedule-item mb-2">
                                <div class="schedule-klinik"><i class="fas fa-hospital text-primary me-1"></i> <?= e($j['nama_klinik']) ?></div>
                                <div class="schedule-detail">
                                    <span class="badge badge-hari bg-primary text-white me-1"><?= e($j['hari']) ?></span>
                                    <?= e($j['jam_mulai']) ?> - <?= e($j['jam_selesai']) ?> WIB
                                    <span class="ms-2">Kuota: <?= (int)$j['kuota'] ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <p class="mb-3"><i class="fas fa-money-bill-wave me-2 text-primary"></i> <strong>Biaya:</strong> <?= rupiah($d['biaya_konsultasi']) ?> / konsultasi</p>
                        </div>
                        <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary w-100">Booking Sekarang</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="dokterEmpty" class="empty-state" style="display:none;">
            <i class="fas fa-user-md"></i>
            <p>Tidak ada dokter di kota ini.</p>
        </div>
    </div>
</section>

<section id="klinik" class="py-5" style="background:var(--bg);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title fw-bold">Data Klinik</h2>
            <p class="section-subtitle">Berikut adalah jaringan klinik yang terdaftar di Klinik Sehat.</p>
        </div>
        <div id="klinikFilter" class="text-center mb-4" style="display:none;">
            <span class="filter-label"><i class="fas fa-filter me-1"></i> Filter: </span>
            <span id="filterKotaKlinik" class="badge bg-primary"></span>
            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="resetFilter()">Reset</button>
        </div>
        <div class="row g-4" id="klinikList">
            <?php foreach ($kliniks as $k): ?>
            <div class="col-md-6 col-lg-4 card-clinic" data-kota="<?= strtolower(e($k['nama_kota'])) ?>">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-hospital text-primary fs-4"></i>
                            <h6 class="mb-0 klinik-nama fw-bold"><?= e($k['nama_klinik']) ?></h6>
                        </div>
                        <div class="klinik-info">
                            <p class="mb-1 text-muted"><i class="fas fa-map-marker-alt me-2"></i> <?= e($k['alamat']) ?>, <?= e($k['nama_kota']) ?></p>
                            <p class="mb-1 text-muted"><i class="fas fa-phone me-2"></i> <?= e($k['no_telp']) ?></p>
                            <p class="mb-0 text-muted"><i class="fas fa-envelope me-2"></i> <?= e($k['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="klinikEmpty" class="empty-state" style="display:none;">
            <i class="fas fa-hospital"></i>
            <p>Tidak ada klinik di kota ini.</p>
        </div>
    </div>
</section>

<footer id="kontak" class="footer footer-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="footer-title fw-bold mb-3">Tentang Klinik Sehat</h5>
                <p class="footer-text">Klinik Sehat adalah layanan kesehatan terpadu yang menghubungkan pasien dengan dokter spesialis terpercaya. Kami menyediakan konsultasi medis, booking jadwal dokter, dan pemesanan obat secara online untuk kenyamanan Anda.</p>
            </div>
            <div class="col-md-4">
                <h5 class="footer-title fw-bold mb-3">Kota yang Terakses</h5>
                <div class="row row-cols-2 row-cols-sm-3 g-2">
                    <?php foreach ($kotas as $kt): ?>
                        <div class="col"><span class="footer-city" onclick="filterByCity('<?= e($kt['nama_kota']) ?>', this)"><?= e($kt['nama_kota']) ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="footer-title fw-bold mb-3">Kontak</h5>
                <p class="mb-2 footer-text"><i class="fas fa-building me-2"></i> <strong>Klinik Sehat</strong></p>
                <p class="mb-1 footer-text"><i class="fas fa-map-marker-alt me-2"></i> Jl. Sudirman No. 10, Jakarta Pusat 12190</p>
                <p class="mb-1 footer-text"><i class="fas fa-phone me-2"></i> (021) 555-100</p>
                <p class="mb-3 footer-text"><i class="fas fa-envelope me-2"></i> info@clinicsehat.id</p>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center small">
            &copy; <?= date('Y') ?> Klinik Sehat &middot; Sistem Pemesanan Apotek &middot; Hak Cipta Dilindungi
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
function filterByCity(kota, el) {
    document.querySelectorAll('.footer-city').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    const k = kota.toLowerCase();
    let dokterVisible = 0, klinikVisible = 0;

    document.querySelectorAll('.card-doctor').forEach(card => {
        const match = card.dataset.kota.includes(k);
        card.classList.toggle('hidden-card', !match);
        if (match) dokterVisible++;
    });

    document.querySelectorAll('.card-clinic').forEach(card => {
        const match = card.dataset.kota.includes(k);
        card.classList.toggle('hidden-card', !match);
        if (match) klinikVisible++;
    });

    document.getElementById('dokterFilter').style.display = 'block';
    document.getElementById('klinikFilter').style.display = 'block';
    document.getElementById('filterKotaDokter').textContent = kota;
    document.getElementById('filterKotaKlinik').textContent = kota;

    document.getElementById('dokterEmpty').style.display = dokterVisible ? 'none' : 'block';
    document.getElementById('klinikEmpty').style.display = klinikVisible ? 'none' : 'block';

    document.getElementById('dokter').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFilter() {
    document.querySelectorAll('.footer-city').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.card-doctor, .card-clinic').forEach(card => card.classList.remove('hidden-card'));
    document.getElementById('dokterFilter').style.display = 'none';
    document.getElementById('klinikFilter').style.display = 'none';
    document.getElementById('dokterEmpty').style.display = 'none';
    document.getElementById('klinikEmpty').style.display = 'none';
    document.getElementById('dokter').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>
</body>
</html>
