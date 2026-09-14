<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/connection.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('dashboard/dashboard.php'));
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id,nama,username,password,level FROM user WHERE username=? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if ($u && ($password === $u['password'] || password_verify($password, $u['password']))) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['nama'] = $u['nama'];
        $_SESSION['level'] = $u['level'];
        header('Location: ' . url('dashboard/dashboard.php'));
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Perpustakaan</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>

<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon">📚</div>
            <div><b>Perpustakaan</b><small>Sistem Peminjaman Buku</small></div>
        </div>
        <h1>Selamat Datang</h1>
        <p class="muted">Silakan masuk ke akun Anda.</p><?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?><form method="post"><label>Username</label><input name="username" required autocomplete="username"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button class="btn primary full">Login</button></form>
        <p class="login-note">Belum punya akun? <a href="<?= e(url('auth/daftar.php')) ?>">Daftar sebagai user</a></p>
    </div>
</body>

</html>