<?php
/* ============================================================
   functions/functions.php
   Kumpulan helper: auth, csrf, sanitasi, upload, format, dll.
   Global $pdo tersedia (di-include setelah koneksi.php)
   ============================================================ */

/* -------------------- AUTH -------------------- */
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}
function is_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . 'auth/login.php');
    }
}
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect(BASE_URL . 'user/');
    }
}
function redirect($url) {
    if (empty($url)) {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
    }
    header('Location: ' . $url);
    exit;
}

/* -------------------- CSRF -------------------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function csrf_valid() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf_token']);
}
function csrf_get_valid() {
    return isset($_GET['csrf_token']) && hash_equals($_SESSION['csrf'] ?? '', $_GET['csrf_token']);
}

/* -------------------- SANITASI / OUTPUT -------------------- */
function e($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}
function clean($str) {
    return trim(strip_tags((string)($str ?? '')));
}
function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/* -------------------- FORMAT -------------------- */
function rupiah($n) {
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
function tgl_indo($datetime) {
    if (empty($datetime)) return '-';
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $d = date('d', strtotime($datetime));
    $m = $bulan[(int)date('m', strtotime($datetime)) - 1];
    $y = date('Y', strtotime($datetime));
    return $d . ' ' . $m . ' ' . $y;
}
function tgl_waktu($datetime) {
    if (empty($datetime)) return '-';
    return date('d M Y H:i', strtotime($datetime));
}

/* -------------------- FLASH MESSAGE -------------------- */
function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/* -------------------- KODE OTOMATIS -------------------- */
function generate_kode_obat($pdo) {
    $stmt = $pdo->query("SELECT kode_obat FROM obat ORDER BY id_obat DESC LIMIT 1");
    $row = $stmt->fetch();
    $no = 1;
    if ($row) {
        $no = (int)substr($row['kode_obat'], 3) + 1;
    }
    return 'OB-' . str_pad($no, 5, '0', STR_PAD_LEFT);
}
function generate_kode_pesanan($pdo) {
    $today = date('Ymd');
    $stmt = $pdo->prepare("SELECT kode_pesanan FROM pesanan WHERE kode_pesanan LIKE ? ORDER BY id_pesanan DESC LIMIT 1");
    $stmt->execute(['PSN-' . $today . '-%']);
    $row = $stmt->fetch();
    $no = 1;
    if ($row) {
        $no = (int)substr($row['kode_pesanan'], -3) + 1;
    }
    return 'PSN-' . $today . '-' . str_pad($no, 3, '0', STR_PAD_LEFT);
}
function generate_kode_invoice($pdo) {
    $today = date('Ym');
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM pesanan WHERE kode_pesanan LIKE ?");
    $stmt->execute(['INV-' . $today . '-%']);
    $no = $stmt->fetchColumn() + 1;
    return 'INV-' . $today . '-' . str_pad($no, 4, '0', STR_PAD_LEFT);
}

/* -------------------- NOTIFIKASI -------------------- */
function send_notif($pdo, $id_user, $judul, $isi) {
    $stmt = $pdo->prepare("INSERT INTO notifikasi (id_user, judul, isi, status, created_at) VALUES (?,?,?,'belum dibaca',NOW())");
    return $stmt->execute([$id_user, $judul, $isi]);
}

/* -------------------- KERANJANG -------------------- */
function cart_count($pdo, $id_user) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE id_user = ?");
    $stmt->execute([$id_user]);
    return (int)$stmt->fetchColumn();
}
function notif_count($pdo, $id_user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE id_user = ? AND status = 'belum dibaca'");
    $stmt->execute([$id_user]);
    return (int)$stmt->fetchColumn();
}

/* -------------------- UPLOAD FILE -------------------- */
function upload_file($file, $dest_dir, $allowed = ['jpg','jpeg','png','gif','webp'], $max_size = 2097152) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'msg' => 'Tidak ada file yang diunggah'];
    }
    $name = $file['name'];
    $tmp  = $file['tmp_name'];
    $size = $file['size'];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        return ['success' => false, 'msg' => 'Tipe file tidak diizinkan (' . implode(',', $allowed) . ')'];
    }
    if ($size > $max_size) {
        return ['success' => false, 'msg' => 'Ukuran file maksimal ' . ($max_size / 1048576) . ' MB'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);
    $allowed_mimes = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'webp' => 'image/webp'
    ];
    if ($mime !== ($allowed_mimes[$ext] ?? '')) {
        return ['success' => false, 'msg' => 'Format file tidak sesuai (detected: ' . $mime . ')'];
    }
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0777, true);
    }
    $newname = uniqid('', true) . '.' . $ext;
    if (!move_uploaded_file($tmp, $dest_dir . $newname)) {
        return ['success' => false, 'msg' => 'Gagal memindahkan file'];
    }
    return ['success' => true, 'file' => $newname];
}

/* -------------------- BADGE STATUS -------------------- */
function status_badge($status) {
    $map = [
        'Menunggu Pembayaran' => 'bg-warning text-dark',
        'Diproses'            => 'bg-info text-dark',
        'Dikirim'             => 'bg-primary',
        'Selesai'             => 'bg-success',
        'Dibatalkan'          => 'bg-secondary',
        'Menunggu'            => 'bg-warning text-dark',
        'Lunas'               => 'bg-success',
        'Ditolak'             => 'bg-danger',
        'Dikemas'             => 'bg-info text-dark',
        'Diterima'            => 'bg-success',
        'Gagal'               => 'bg-danger',
        'belum dibaca'        => 'bg-danger',
        'dibaca'              => 'bg-secondary',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . e($status) . '</span>';
}
