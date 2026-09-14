<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/connection.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('dashboard/dashboard.php'));
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($nama === '' || $username === '' || $password === '') $error = 'Semua data wajib diisi.';
    else {
        $cek = $conn->prepare('SELECT id FROM user WHERE username=?');
        $cek->bind_param('s', $username);
        $cek->execute();
        if ($cek->get_result()->num_rows) $error = 'Username sudah digunakan.';
        else {
            $stmt = $conn->prepare("INSERT INTO user(nama,username,password,level) VALUES(?,?,?,'user')");
            $stmt->bind_param('sss', $nama, $username, $password);
            if ($stmt->execute()) {
                $success = 'Pendaftaran berhasil. Silakan login.';
            } else $error = 'Pendaftaran gagal: ' . $stmt->error;
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>

<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon">📚</div>
            <div><b>Perpustakaan</b><small>Buat Akun User</small></div>
        </div>
        <h1>Daftar Akun</h1><?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?><form method="post"><label>Nama</label><input name="nama" required><label>Username</label><input name="username" required><label>Password</label><input type="password" name="password" required><button class="btn primary full">Daftar</button></form>
        <p class="login-note"><a href="<?= e(url('auth/login.php')) ?>">Kembali ke login</a></p>
    </div>
</body>

</html>