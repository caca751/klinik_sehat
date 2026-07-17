<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Pengaturan';
$active = 'pengaturan';

$pdo->exec("CREATE TABLE IF NOT EXISTS pengaturan (
    id INT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
    nama_app VARCHAR(100) DEFAULT 'Clinic Sehat',
    email VARCHAR(100) DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("INSERT IGNORE INTO pengaturan (id) VALUES (1)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    if (isset($_POST['save_app'])) {
        $pdo->prepare("UPDATE pengaturan SET nama_app=?, email=?, telepon=?, alamat=? WHERE id=1")
            ->execute([clean($_POST['nama_app']), clean($_POST['email']), clean($_POST['telepon']), clean($_POST['alamat'])]);
        set_flash('success', 'Pengaturan disimpan.');
    }
    if (isset($_POST['save_profile'])) {
        $nama = clean($_POST['nama']);
        $email = clean($_POST['email']);
        if (!valid_email($email)) { set_flash('error', 'Email tidak valid.'); redirect(''); }
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE users SET nama=?, email=?, password=? WHERE id_user=?")
                ->execute([$nama, $email, password_hash($_POST['password'], PASSWORD_DEFAULT), $_SESSION['user']['id_user']]);
        } else {
            $pdo->prepare("UPDATE users SET nama=?, email=? WHERE id_user=?")->execute([$nama, $email, $_SESSION['user']['id_user']]);
        }
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['email'] = $email;
        set_flash('success', 'Profil diperbarui.');
    }
    if (isset($_POST['save_theme'])) {
        $_SESSION['dark_mode'] = $_POST['dark_mode'] === 'dark' ? 'dark' : 'light';
        set_flash('success', 'Tema disimpan.');
    }
    redirect('');
}

$set = $pdo->query("SELECT * FROM pengaturan WHERE id=1")->fetch();
$set = $set ?: [];
$adm = $pdo->prepare("SELECT * FROM users WHERE id_user=?");
$adm->execute([$_SESSION['user']['id_user']]); $adm = $adm->fetch();
$adm = $adm ?: [];

require_once '../../includes/header_admin.php';
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Profil Admin</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" value="<?= e($adm['nama']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($adm['email']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label><input type="password" name="password" class="form-control"></div>
                    <button class="btn btn-primary" name="save_profile">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Pengaturan Aplikasi</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Nama Apotek</label><input type="text" name="nama_app" class="form-control" value="<?= e($set['nama_app']) ?>"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($set['email']) ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control" value="<?= e($set['telepon']) ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2"><?= e($set['alamat']) ?></textarea></div>
                    <button class="btn btn-primary" name="save_app">Simpan Pengaturan</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Tema Default</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <select name="dark_mode" class="form-select mb-2" style="max-width:220px">
                        <option value="light" <?= ($_SESSION['dark_mode'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light</option>
                        <option value="dark" <?= ($_SESSION['dark_mode'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    </select>
                    <button class="btn btn-secondary" name="save_theme">Simpan Tema</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>
