<?php
require_once '../config/koneksi.php';

$page_title = 'Login';
if (is_logged_in()) {
    redirect(is_admin() ? BASE_URL . 'admin/' : BASE_URL . 'user/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { $error = 'Token keamanan tidak valid.'; }
    else {
        $email = clean($_POST['email']);
        $pass  = $_POST['password'];
        if (!valid_email($email)) { $error = 'Format email tidak valid.'; }
        else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            if ($u && password_verify($pass, $u['password'])) {
                $_SESSION['user'] = [
                    'id_user' => $u['id_user'],
                    'nama'    => $u['nama'],
                    'email'   => $u['email'],
                    'role'    => $u['role'],
                ];
                set_flash('success', 'Selamat datang, ' . $u['nama']);
                redirect($u['role'] === 'admin' ? BASE_URL . 'admin/' : BASE_URL . 'user/');
            } else {
                $error = 'Email atau password salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Klinik Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="head">
            <img src="<?= BASE_URL ?>assets/images/logo.svg?v=2" width="48"><br>
            <h4 class="mt-2 mb-0">Klinik Sehat</h4>
            <small>Sistem Pemesanan Obat Online</small>
        </div>
        <div class="body">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="email@mail.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                <button class="btn btn-primary w-100 py-2"><i class="fas fa-right-to-bracket me-1"></i> Masuk</button>
            </form>
            <div class="mt-3 text-center">
                <a href="<?= BASE_URL ?>auth/lupa_password.php">Lupa Password?</a>
            </div>
        </div>
        <div class="foot">
            Belum punya akun? <a href="<?= BASE_URL ?>auth/register.php">Daftar di sini</a>
        </div>
    </div>
</div>
</body>
</html>
