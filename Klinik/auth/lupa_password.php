<?php
require_once '../config/koneksi.php';

if (is_logged_in()) redirect(BASE_URL . 'user/');

$error = '';
$info = '';

if (isset($_POST['send'])) {
    if (!csrf_valid()) { $error = 'Token keamanan tidak valid.'; }
    else {
        $email = clean($_POST['email']);
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_email'] = $email;
            $info = 'Jika email terdaftar, kami akan mengirimkan instruksi reset password ke alamat tersebut.';
        } else {
            $error = 'Email tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password | Clinic Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="head">
            <img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="48"><br>
            <h4 class="mt-2 mb-0">Lupa Password</h4>
            <small>Masukkan email untuk reset password</small>
        </div>
        <div class="body">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($info): ?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@mail.com">
                </div>
                <button class="btn btn-primary w-100" name="send"><i class="fas fa-paper-plane me-1"></i> Kirim Link Reset</button>
            </form>
        </div>
        <div class="foot"><a href="<?= BASE_URL ?>auth/login.php">Kembali ke Login</a></div>
    </div>
</div>
</body>
</html>
