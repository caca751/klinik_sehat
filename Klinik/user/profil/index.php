<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Profil Saya';
$active = 'dashboard';
$uid = $_SESSION['user']['id_user'];

$profileStmt = $pdo->prepare("SELECT id_user, nama, email, password, COALESCE(no_hp, '') AS no_hp, COALESCE(alamat, '') AS alamat, birthdate, role, created_at FROM users WHERE id_user=?");
$profileStmt->execute([$uid]);
$profile = $profileStmt->fetch();
if (empty($profile) || !is_array($profile)) {
    set_flash('error', 'Profil pengguna tidak ditemukan.');
    redirect(BASE_URL . 'auth/logout.php');
}
if (!array_key_exists('birthdate', $profile)) {
    $profile['birthdate'] = '';
}
$birthdate = $profile['birthdate'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    if (isset($_POST['update_profil'])) {
        $nama = clean($_POST['nama'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $hp = clean($_POST['no_hp'] ?? '');
        $alamat = clean($_POST['alamat'] ?? '');
        $birthdateInput = trim($_POST['birthdate'] ?? '');
        $birthdate = null;
        if ($birthdateInput !== '') {
            $dt = DateTime::createFromFormat('Y-m-d', $birthdateInput);
            if (!$dt || $dt->format('Y-m-d') !== $birthdateInput) {
                set_flash('error', 'Format tanggal lahir tidak valid.'); redirect('');
            }
            if ($dt > new DateTime()) {
                set_flash('error', 'Tanggal lahir tidak boleh di masa depan.'); redirect('');
            }
            $birthdate = $birthdateInput;
        }
        if (!valid_email($email)) { set_flash('error', 'Email tidak valid.'); redirect(''); }
        $cek = $pdo->prepare("SELECT id_user FROM users WHERE email=? AND id_user!=?");
        $cek->execute([$email, $uid]);
        if ($cek->fetch()) { set_flash('error', 'Email sudah dipakai.'); redirect(''); }
        $pdo->prepare("UPDATE users SET nama=?, email=?, no_hp=?, alamat=?, birthdate=? WHERE id_user=?")->execute([$nama, $email, $hp, $alamat, $birthdate, $uid]);
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['email'] = $email;
        set_flash('success', 'Profil diperbarui.');
    }
    if (isset($_POST['ganti_pass'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $new2 = $_POST['new_password2'];
        if (!password_verify($old, $profile['password'] ?? '')) { set_flash('error', 'Password lama salah.'); redirect(''); }
        if (strlen($new) < 6) { set_flash('error', 'Password baru minimal 6 karakter.'); redirect(''); }
        if ($new !== $new2) { set_flash('error', 'Konfirmasi tidak cocok.'); redirect(''); }
        $pdo->prepare("UPDATE users SET password=? WHERE id_user=?")->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        set_flash('success', 'Password diubah.');
    }
    redirect('');
}

require_once '../../includes/header_user.php';
$age = $birthdate ? date_diff(new DateTime($birthdate), new DateTime())->y : null;
?>
<div class="row g-4 justify-content-center">
    <div class="col-12 col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center px-4 py-5">
                <div class="avatar mx-auto mb-4" style="width:110px;height:110px;font-size:40px;line-height:110px;"><?= strtoupper(substr($profile['nama'],0,1)) ?></div>
                <h4 class="mb-1"><?= e($profile['nama']) ?></h4>
                <span class="badge bg-primary py-2 px-3 text-uppercase fs-7"><?= e($profile['role']) ?></span>
                <div class="my-4" style="max-width:540px;margin:auto;text-align:left;">
                    <p class="mb-2"><i class="fas fa-envelope me-2 text-secondary"></i><strong>Email:</strong> <?= e($profile['email'] ?? '-') ?></p>
                    <p class="mb-2"><i class="fas fa-phone me-2 text-secondary"></i><strong>Telepon:</strong> <?= e($profile['no_hp'] ?? '-') ?></p>
                    <p class="mb-2"><i class="fas fa-location-dot me-2 text-secondary"></i><strong>Alamat:</strong> <?= e($profile['alamat'] ?? '-') ?></p>
                    <p class="mb-2"><i class="fas fa-user-clock me-2 text-secondary"></i><strong>Usia:</strong> <?= $age !== null ? $age . ' tahun' : '-' ?></p>
                    <p class="mb-2"><i class="fas fa-calendar-days me-2 text-secondary"></i><strong>Tanggal Lahir:</strong> <?= !empty($birthdate) ? tgl_indo($birthdate) : '-' ?></p>
                    <p class="mt-3 mb-0 text-muted"><small>Bergabung: <?= tgl_indo($profile['created_at'] ?? '') ?></small></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-8">
        <div class="row gx-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card">
                    <div class="card-header">Edit Profil</div>
                    <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" value="<?= e($profile['nama'] ?? '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">No. HP</label><input type="text" name="no_hp" class="form-control" value="<?= e($profile['no_hp'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Tanggal Lahir</label><input type="date" name="birthdate" class="form-control" value="<?= e($profile['birthdate'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2"><?= e($profile['alamat'] ?? '') ?></textarea></div>
                    <button class="btn btn-primary" name="update_profil">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Ganti Password</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Password Lama</label><input type="password" name="old_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password Baru</label><input type="password" name="new_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Konfirmasi Password Baru</label><input type="password" name="new_password2" class="form-control" required></div>
                    <button class="btn btn-warning" name="ganti_pass">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
