<?php
require_once '../config/koneksi.php';
session_destroy();
session_start();
set_flash('info', 'Anda telah logout.');
redirect(BASE_URL . 'auth/login.php');
