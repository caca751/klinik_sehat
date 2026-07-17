<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Notifikasi';
$active = 'dashboard';
$uid = $_SESSION['user']['id_user'];

$notif = $pdo->prepare("SELECT * FROM notifikasi WHERE id_user=? ORDER BY created_at DESC");
$notif->execute([$uid]);

require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-bell me-2"></i>Notifikasi</span>
        <form method="post" action="<?= BASE_URL ?>api/notifikasi.php" class="d-inline"><?= csrf_field() ?><input type="hidden" name="act" value="read_all"><button type="submit" class="btn btn-sm btn-outline-primary">Tandai Semua Dibaca</button></form>
    </div>
    <div class="card-body p-0">
        <?php
        $notif = $pdo->prepare("SELECT * FROM notifikasi WHERE id_user=? ORDER BY created_at DESC");
        $notif->execute([$uid]);
        $notif_list = $notif->fetchAll();
        ?>
        <?php if (empty($notif_list)): ?><div class="empty-state"><i class="fas fa-bell-slash"></i><p>Tidak ada notifikasi.</p></div><?php endif; ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($notif_list as $n): ?>
            <li class="list-group-item d-flex gap-2 <?= $n['status']==='belum dibaca'?'bg-primary bg-opacity-10':'' ?>">
                <i class="fas fa-circle text-<?= $n['status']==='belum dibaca'?'primary':'secondary' ?> mt-1"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($n['judul']) ?></div>
                    <div class="small text-muted-2"><?= e($n['isi']) ?></div>
                    <div class="small text-muted"><?= tgl_waktu($n['created_at']) ?></div>
                </div>
                <?php if ($n['status']==='belum dibaca'): ?>
                <form method="post" action="<?= BASE_URL ?>api/notifikasi.php" class="d-inline"><?= csrf_field() ?><input type="hidden" name="act" value="read"><input type="hidden" name="id" value="<?= $n['id_notifikasi'] ?>"><button type="submit" class="btn btn-sm btn-link">Tandai</button></form>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
