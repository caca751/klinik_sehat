<?php
require_once '../config/koneksi.php';
require_login();
$uid = $_SESSION['user']['id_user'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $input = [];
    if (!empty($_POST)) {
        $input = $_POST;
    } else {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
    }

    $token = $input['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        echo json_encode(['status' => 'error', 'msg' => 'Token CSRF tidak valid']);
        exit;
    }

    $act = $input['act'] ?? '';
    $id = (int)($input['id'] ?? 0);

    if ($act === 'read' && $id > 0) {
        $pdo->prepare("UPDATE notifikasi SET status='dibaca' WHERE id_notifikasi=? AND id_user=?")->execute([$id, $uid]);
        echo json_encode(['status' => 'ok']);
        exit;
    } elseif ($act === 'read_all') {
        $pdo->prepare("UPDATE notifikasi SET status='dibaca' WHERE id_user=?")->execute([$uid]);
        echo json_encode(['status' => 'ok']);
        exit;
    } elseif ($act === 'count') {
        echo json_encode(['status' => 'ok', 'count' => notif_count($pdo, $uid)]);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak dikenal']);
    exit;
}

if (isset($_GET['act'])) {
    $act = $_GET['act'];
    if ($act === 'read' && isset($_GET['id'])) {
        $pdo->prepare("UPDATE notifikasi SET status='dibaca' WHERE id_notifikasi=? AND id_user=?")->execute([(int)$_GET['id'], $uid]);
    } elseif ($act === 'read_all') {
        $pdo->prepare("UPDATE notifikasi SET status='dibaca' WHERE id_user=?")->execute([$uid]);
    }
    redirect(BASE_URL . 'user/notifikasi/');
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'count' => notif_count($pdo, $uid)]);
