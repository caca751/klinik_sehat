<?php
require_once '../config/koneksi.php';

if (is_logged_in()) redirect(BASE_URL . 'user/');

$error = '';
$valid = false;

if (!isset($_GET['token']) || !isset($_SESSION['reset_token']) || $_SESSION['reset_token'] !== $_GET['token']) {
    $error = 'Token tidak valid atau kadaluarsa.';
} else {
    $valid = true;
}

if ($valid && isset($_POST['reset'])) {
    if (!csrf_valid()) { $error = 'Token tidak valid.'; }
    else {
        $pass = $_POST['password'];
        $pass2 = $_POST['password2'];
        if (strlen($pass) < 6) $error = 'Password minimal 6 karakter.';
        elseif ($pass !== $pass2) $error = 'Konfirmasi tidak cocok.';
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->execute([$hash, $_SESSION['reset_email']]);
            unset($_SESSION['reset_token'], $_SESSION['reset_email']);
            set_flash('success', 'Password berhasil diubah.');
            redirect(BASE_URL . 'auth/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Klinik Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="head">
            <img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="48"><br>
            <h4 class="mt-2 mb-0">Reset Password</h4>
        </div>
        <div class="body">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($valid): ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password2" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100" name="reset">Ubah Password</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="foot"><a href="<?= BASE_URL ?>auth/login.php">Kembali ke Login</a></div>
    </div>
</div>
</body>
</html>
