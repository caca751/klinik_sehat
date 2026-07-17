<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Review Obat';
$active = 'review';
$uid = $_SESSION['user']['id_user'];

$obats = $pdo->query("SELECT id_obat, nama_obat FROM obat ORDER BY nama_obat")->fetchAll();
$preObat = (int)($_GET['id_obat'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    $id_obat = (int)$_POST['id_obat'];
    $rating = (int)$_POST['rating'];
    $komentar = clean($_POST['komentar']);
    if ($rating < 1 || $rating > 5) { set_flash('error', 'Rating 1-5.'); redirect(''); }
    if (!$id_obat) { set_flash('error', 'Pilih obat.'); redirect(''); }
    $cek = $pdo->prepare("SELECT id_review FROM review WHERE id_user=? AND id_obat=?");
    $cek->execute([$uid, $id_obat]);
    if ($cek->fetch()) {
        $pdo->prepare("UPDATE review SET rating=?, komentar=?, tanggal=NOW() WHERE id_user=? AND id_obat=?")->execute([$rating, $komentar, $uid, $id_obat]);
        set_flash('success', 'Review diperbarui.');
    } else {
        $pdo->prepare("INSERT INTO review (id_user, id_obat, rating, komentar, tanggal) VALUES (?,?,?,?,NOW())")->execute([$uid, $id_obat, $rating, $komentar]);
        set_flash('success', 'Review ditambahkan.');
    }
    redirect('');
}

$my = $pdo->prepare("SELECT r.*, o.nama_obat FROM review r JOIN obat o ON o.id_obat=r.id_obat WHERE r.id_user=? ORDER BY r.tanggal DESC");
$my->execute([$uid]);
$my = $my->fetchAll();

require_once '../../includes/header_user.php';
?>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card sticky-summary">
            <div class="card-header"><i class="fas fa-star me-2"></i>Beri Review</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Obat</label>
                        <select name="id_obat" class="form-select" required>
                            <option value="">-- Pilih Obat --</option>
                            <?php foreach ($obats as $o): ?><option value="<?= $o['id_obat'] ?>" <?= $o['id_obat']==$preObat?'selected':'' ?>><?= e($o['nama_obat']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            <option value="5">★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Komentar</label>
                        <textarea name="komentar" class="form-control" rows="3" required></textarea></div>
                    <button class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i> Kirim Review</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Review Saya</div>
            <div class="card-body">
                <?php if (empty($my)): ?><p class="text-muted">Belum ada review.</p><?php endif; ?>
                <?php foreach ($my as $rv): ?>
                <div class="mb-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between"><strong><?= e($rv['nama_obat']) ?></strong><span class="text-warning"><?= str_repeat('★', $rv['rating']) ?></span></div>
                    <div class="small text-muted-2"><?= e($rv['komentar']) ?></div>
                    <div class="small text-muted"><?= tgl_waktu($rv['tanggal']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
