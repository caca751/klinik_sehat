<?php
/* includes/header_admin.php
   Layout admin: head + sidebar + topbar + content open */
$user = current_user();
$active = $active ?? '';
$cart = 0;
$notifCount = notif_count($pdo, $user['id_user']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Admin') ?> | Klinik Sehat</title>
    <link href="<?= BASE_URL ?>assets/images/logo.svg?v=2" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <script>window.APP_BASE_URL='<?= BASE_URL ?>';window.APP_DEFAULT_THEME='<?= ($_SESSION['dark_mode'] ?? 'light') ?>';window.CSRF='<?= csrf_token() ?>';</script>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="brand"><img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="30"> Klinik Sehat</div>
        <div class="nav-section">Utama</div>
        <a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="<?= BASE_URL ?>admin/"><i class="fas fa-gauge-high"></i> Dashboard</a>
        <div class="nav-section">Data Master</div>
        <a class="nav-link <?= $active==='user'?'active':'' ?>" href="<?= BASE_URL ?>admin/user/"><i class="fas fa-users"></i> Data User</a>
        <a class="nav-link <?= $active==='obat'?'active':'' ?>" href="<?= BASE_URL ?>admin/obat/"><i class="fas fa-pills"></i> Data Obat</a>
        <a class="nav-link <?= $active==='supplier'?'active':'' ?>" href="<?= BASE_URL ?>admin/supplier/"><i class="fas fa-truck"></i> Data Supplier</a>
        <a class="nav-link <?= $active==='kategori'?'active':'' ?>" href="<?= BASE_URL ?>admin/kategori/"><i class="fas fa-tags"></i> Data Kategori</a>
        <a class="nav-link <?= $active==='apotek'?'active':'' ?>" href="<?= BASE_URL ?>admin/apotek/"><i class="fas fa-building"></i> Data Apotek</a>
        <div class="nav-section">Klinik &amp; Dokter</div>
        <a class="nav-link <?= $active==='kota'?'active':'' ?>" href="<?= BASE_URL ?>admin/kota/"><i class="fas fa-map-marker-alt"></i> Kota</a>
        <a class="nav-link <?= $active==='spesialis'?'active':'' ?>" href="<?= BASE_URL ?>admin/spesialis/"><i class="fas fa-stethoscope"></i> Spesialis</a>
        <a class="nav-link <?= $active==='klinik'?'active':'' ?>" href="<?= BASE_URL ?>admin/klinik/"><i class="fas fa-hospital"></i> Klinik</a>
        <a class="nav-link <?= $active==='dokter'?'active':'' ?>" href="<?= BASE_URL ?>admin/dokter/"><i class="fas fa-user-md"></i> Dokter</a>
        <a class="nav-link <?= $active==='jadwal'?'active':'' ?>" href="<?= BASE_URL ?>admin/jadwal/"><i class="fas fa-calendar-alt"></i> Jadwal Praktik</a>
        <a class="nav-link <?= $active==='booking'?'active':'' ?>" href="<?= BASE_URL ?>admin/booking/"><i class="fas fa-calendar-check"></i> Booking</a>
        <div class="nav-section">Transaksi</div>
        <a class="nav-link <?= $active==='pesanan'?'active':'' ?>" href="<?= BASE_URL ?>admin/pesanan/"><i class="fas fa-shopping-cart"></i> Kelola Pesanan</a>
        <a class="nav-link <?= $active==='pembayaran'?'active':'' ?>" href="<?= BASE_URL ?>admin/pembayaran/"><i class="fas fa-money-bill-wave"></i> Kelola Pembayaran</a>
        <a class="nav-link <?= $active==='metode_pembayaran'?'active':'' ?>" href="<?= BASE_URL ?>admin/metode_pembayaran/"><i class="fas fa-wallet"></i> Metode Pembayaran</a>
        <a class="nav-link <?= $active==='pengiriman'?'active':'' ?>" href="<?= BASE_URL ?>admin/pengiriman/"><i class="fas fa-box"></i> Kelola Pengiriman</a>
        <a class="nav-link <?= $active==='review'?'active':'' ?>" href="<?= BASE_URL ?>admin/review/"><i class="fas fa-star"></i> Kelola Review</a>
        <div class="nav-section">Lainnya</div>
        <a class="nav-link <?= $active==='laporan'?'active':'' ?>" href="<?= BASE_URL ?>admin/laporan/"><i class="fas fa-file-alt"></i> Laporan</a>
        <a class="nav-link <?= $active==='pengaturan'?'active':'' ?>" href="<?= BASE_URL ?>admin/pengaturan/"><i class="fas fa-gear"></i> Pengaturan</a>
        <a class="nav-link" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="toggle"><i class="fas fa-bars"></i></button>
                <strong><?= e($page_title ?? 'Dashboard') ?></strong>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button id="darkToggle" type="button" title="Mode Gelap"><i class="fas fa-moon"></i> <span class="mode-label">Gelap</span></button>
                <div class="dropdown notification-dropdown">
                    <a class="text-dark" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell fs-5"></i>
                        <?php if ($notifCount > 0): ?><span class="badge bg-danger rounded-pill" id="notifBadge"><?= $notifCount ?></span><?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-2 notification-menu">
                        <li class="dropdown-header d-flex justify-content-between"><span>Notifikasi</span><a href="#" class="small" onclick="markAllRead()">Tandai semua</a></li>
                        <?php
                        $ntf = $pdo->prepare("SELECT * FROM notifikasi WHERE id_user=? ORDER BY created_at DESC LIMIT 5");
                        $ntf->execute([$user['id_user']]);
                        foreach ($ntf as $n): ?>
                            <li><a class="dropdown-item" href="#" onclick="markNotifRead(<?= $n['id_notifikasi'] ?>)"><div class="fw-semibold"><?= e($n['judul']) ?></div><small class="text-muted" style="word-break:break-word"><?= e($n['isi']) ?></small></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-dark" href="#" data-bs-toggle="dropdown">
                        <span class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></span>
                        <span class="d-none d-md-inline"><?= e($user['nama']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/pengaturan/"><i class="fas fa-gear me-2"></i>Pengaturan</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="content">
            <?php $f = get_flash(); if ($f): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($f['msg']) ?>,'<?= $f['type'] ?>'));</script>
            <?php endif; ?>
            <div class="breadcrumb-custom">
                <a href="<?= BASE_URL ?>admin/"><i class="fas fa-house"></i> Home</a>
                <span class="sep">/</span><span><?= e($page_title ?? '') ?></span>
            </div>
