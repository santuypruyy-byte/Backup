<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$data = [
    'nama' => '',
    'username' => '',
    'password' => '',
    'level' => 'user',
];
$error = '';

if ($id) {
    $query = $conn->prepare('SELECT id, nama, username, password, level FROM user WHERE id = ?');
    $query->bind_param('i', $id);
    $query->execute();
    $data = $query->get_result()->fetch_assoc() ?: $data;
    $data['password'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama'] = trim($_POST['nama'] ?? '');
    $data['username'] = trim($_POST['username'] ?? '');
    $data['level'] = $_POST['level'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if ($data['nama'] === '' || $data['username'] === '') {
        $error = 'Nama dan username wajib diisi.';
    } elseif (!in_array($data['level'], ['admin', 'user'], true)) {
        $error = 'Level akun tidak valid.';
    } elseif (!$id && $password === '') {
        $error = 'Password wajib diisi untuk user baru.';
    } else {
        $check = $conn->prepare('SELECT id FROM user WHERE username = ? AND id <> ?');
        $check->bind_param('si', $data['username'], $id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } elseif ($id) {
            if ($password !== '') {
                $query = $conn->prepare('UPDATE user SET nama = ?, username = ?, password = ?, level = ? WHERE id = ?');
                $query->bind_param('ssssi', $data['nama'], $data['username'], $password, $data['level'], $id);
            } else {
                $query = $conn->prepare('UPDATE user SET nama = ?, username = ?, level = ? WHERE id = ?');
                $query->bind_param('sssi', $data['nama'], $data['username'], $data['level'], $id);
            }
            $query->execute();

            if ($id === (int) $_SESSION['user_id']) {
                $_SESSION['nama'] = $data['nama'];
                $_SESSION['level'] = $data['level'];
            }

            header('Location: ' . url('user/index.php'));
            exit;
        } else {
            $query = $conn->prepare('INSERT INTO user (nama, username, password, level) VALUES (?, ?, ?, ?)');
            $query->bind_param('ssss', $data['nama'], $data['username'], $password, $data['level']);
            $query->execute();
            header('Location: ' . url('user/index.php'));
            exit;
        }
    }
}

$pageTitle = $id ? 'Edit User' : 'Tambah User';
$pageDescription = $id ? 'Perbarui data akun pengguna.' : 'Tambahkan akun administrator atau user.';
require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($error): ?>
    <div class="alert danger"><?= e($error) ?></div>
<?php endif; ?>

<section class="panel form-panel">
    <div class="form-title">
        <span class="page-label">DATA MASTER</span>
        <h1><?= e($pageTitle) ?></h1>
        <p>Kelola nama, username, password, dan level akun.</p>
    </div>

    <form method="post" class="form-grid">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div>
            <label>Nama</label>
            <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>
        </div>
        <div>
            <label>Username</label>
            <input type="text" name="username" value="<?= e($data['username']) ?>" required>
        </div>
        <div>
            <label>Password <?= $id ? '(kosongkan jika tidak diubah)' : '' ?></label>
            <input type="password" name="password" <?= $id ? '' : 'required' ?> autocomplete="new-password">
        </div>
        <div>
            <label>Level</label>
            <select name="level" required>
                <option value="user" <?= $data['level'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $data['level'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="full form-actions">
            <button type="submit" class="btn primary">Simpan</button>
            <a href="<?= e(url('user/index.php')) ?>" class="btn">Kembali</a>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
