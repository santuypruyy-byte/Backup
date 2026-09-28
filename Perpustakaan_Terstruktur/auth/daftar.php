<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/connection.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('dashboard/dashboard.php'));
    exit;
}

$error = '';
$success = '';

$nama = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$anggotaId = (int) ($_POST['anggota_id'] ?? 0);

$anggotaRows = $conn->query(
    "SELECT a.id, a.kode_anggota, a.nama
     FROM anggota a
     LEFT JOIN user u ON u.anggota_id = a.id
     WHERE u.id IS NULL
     ORDER BY a.nama ASC"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $username === '' || $password === '' || $anggotaId < 1) {
        $error = 'Semua data wajib diisi, termasuk anggota.';
    } elseif (strlen($password) < 5) {
        $error = 'Password minimal 5 karakter.';
    } else {
        $cek = $conn->prepare('SELECT id FROM user WHERE username = ? LIMIT 1');
        $cek->bind_param('s', $username);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $cekAnggota = $conn->prepare(
                'SELECT a.id
                 FROM anggota a
                 LEFT JOIN user u ON u.anggota_id = a.id
                 WHERE a.id = ? AND u.id IS NULL
                 LIMIT 1'
            );
            $cekAnggota->bind_param('i', $anggotaId);
            $cekAnggota->execute();

            if (!$cekAnggota->get_result()->fetch_assoc()) {
                $error = 'Anggota tidak tersedia atau sudah memiliki akun.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    'INSERT INTO user (nama, username, password, level, anggota_id)
                     VALUES (?, ?, ?, \'user\', ?)'
                );
                $stmt->bind_param('sssi', $nama, $username, $passwordHash, $anggotaId);

                if ($stmt->execute()) {
                    $success = 'Pendaftaran berhasil. Silakan login.';
                    $nama = '';
                    $username = '';
                    $anggotaId = 0;
                } else {
                    $error = 'Pendaftaran gagal: ' . $stmt->error;
                }
            }
        }
    }

    // Refresh daftar anggota agar pilihan yang sudah dipakai hilang.
    $anggotaRows = $conn->query(
        "SELECT a.id, a.kode_anggota, a.nama
         FROM anggota a
         LEFT JOIN user u ON u.anggota_id = a.id
         WHERE u.id IS NULL
         ORDER BY a.nama ASC"
    );
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar - Perpustakaan</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon">📚</div>
            <div><b>Perpustakaan</b><small>Buat Akun User</small></div>
        </div>

        <h1>Daftar Akun</h1>

        <?php if ($error): ?>
            <div class="alert danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="nama">Nama</label>
            <input id="nama" name="nama" value="<?= e($nama) ?>" required>

            <label for="anggota_id">Anggota</label>
            <select id="anggota_id" name="anggota_id" required>
                <option value="">Pilih anggota</option>
                <?php while ($a = $anggotaRows->fetch_assoc()): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= $anggotaId === (int) $a['id'] ? 'selected' : '' ?>>
                        <?= e($a['kode_anggota'] . ' - ' . $a['nama']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="username">Username</label>
            <input id="username" name="username" value="<?= e($username) ?>" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required minlength="5" autocomplete="new-password">

            <button class="btn primary full" type="submit">Daftar</button>
        </form>

        <p class="login-note">
            <a href="<?= e(url('auth/login.php')) ?>">Kembali ke login</a>
        </p>
    </div>
</body>
</html>
