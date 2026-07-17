<?php
require_once '../config/koneksi.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'msg' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mode = $data['mode'] ?? '';
$token = $data['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'msg' => 'Token CSRF tidak valid']);
    exit;
}

if (in_array($mode, ['dark', 'light'], true)) {
    $_SESSION['dark_mode'] = $mode;
}
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
