<?php
require_once 'config/koneksi.php';
if (is_logged_in()) {
    redirect(is_admin() ? BASE_URL . 'admin/' : BASE_URL . 'user/');
}
require_once 'dashboard.php';
