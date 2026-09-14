<?php
require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogin() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLogin()) {
        header('Location: ' . url('auth/login.php'));
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (($_SESSION['level'] ?? '') !== 'admin') {
        header('Location: ' . url('dashboard/dashboard.php'));
        exit;
    }
}
