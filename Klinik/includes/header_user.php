<?php
/* includes/header_user.php
   Layout customer: head + navbar + content open */
$user = current_user();
$active = $active ?? '';
$cartCount = cart_count($pdo, $user['id_user']);
$notifCount = notif_count($pdo, $user['id_user']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Klinik Sehat') ?> | Klinik Sehat</title>
    <link href="<?= BASE_URL ?>assets/images/logo.svg?v=2" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <script>window.APP_BASE_URL='<?= BASE_URL ?>';window.APP_DEFAULT_THEME='<?= ($_SESSION['dark_mode'] ?? 'light') ?>';window.CSRF='<?= csrf_token() ?>';</script>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background:var(--surface);border-bottom:1px solid var(--border);">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= BASE_URL ?>user/" style="color:var(--primary)">
            <img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="32"> Klinik Sehat
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navuser">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navuser">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="<?= BASE_URL ?>user/">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='apotek'?'active':'' ?>" href="<?= BASE_URL ?>user/apotek/">Apotek</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='obat'?'active':'' ?>" href="<?= BASE_URL ?>user/obat/">Obat</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='dokter'?'active':'' ?>" href="<?= BASE_URL ?>user/dokter/">Dokter</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='booking'?'active':'' ?>" href="<?= BASE_URL ?>user/booking/">Booking</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='pesanan'?'active':'' ?>" href="<?= BASE_URL ?>user/pesanan/">Pesanan</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='tracking'?'active':'' ?>" href="<?= BASE_URL ?>user/tracking/">Tracking</a></li>
                <li class="nav-item"><a class="nav-link <?= $active==='review'?'active':'' ?>" href="<?= BASE_URL ?>user/review/">Review</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>user/keranjang/" class="text-dark position-relative"><i class="fas fa-cart-shopping fs-5"></i>
                    <span class="badge bg-danger rounded-pill" id="cartBadge" style="display:<?= $cartCount>0?'inline-block':'none' ?>"><?= $cartCount ?></span>
                </a>
                <div class="dropdown notification-dropdown">
                    <a class="text-dark position-relative" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell fs-5"></i>
                        <?php if ($notifCount>0): ?><span class="badge bg-danger rounded-pill"><?= $notifCount ?></span><?php endif; ?>
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
                <button id="darkToggle" type="button" title="Mode Gelap"><i class="fas fa-moon"></i> <span class="mode-label">Gelap</span></button>
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-dark text-decoration-none" href="#" data-bs-toggle="dropdown">
                        <span class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>user/profil/"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>user/notifikasi/"><i class="fas fa-bell me-2"></i>Notifikasi</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<main class="content container-fluid py-4">
    <?php $f = get_flash(); if ($f): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>toast(<?= json_encode($f['msg']) ?>,'<?= $f['type'] ?>'));</script>
    <?php endif; ?>
    <?php if (!empty($page_title)): ?>
    <div class="breadcrumb-custom">
        <a href="<?= BASE_URL ?>user/"><i class="fas fa-house"></i> Home</a>
        <span class="sep">/</span><span><?= e($page_title) ?></span>
    </div>
    <h4 class="mb-3 fw-bold"><?= e($page_title) ?></h4>
    <?php endif; ?>
