<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$msg = '';
$type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $id = (int) $_POST['hapus_id'];

    if ($id === (int) $_SESSION['user_id']) {
        $msg = 'Akun yang sedang digunakan tidak dapat dihapus.';
        $type = 'danger';
    } else {
        $check = $conn->prepare('SELECT COUNT(*) AS total FROM peminjaman WHERE user_id = ?');
        $check->bind_param('i', $id);
        $check->execute();
        $totalPinjam = (int) $check->get_result()->fetch_assoc()['total'];

        if ($totalPinjam > 0) {
            $msg = 'User tidak dapat dihapus karena memiliki riwayat peminjaman.';
            $type = 'danger';
        } else {
            $delete = $conn->prepare('DELETE FROM user WHERE id = ?');
            $delete->bind_param('i', $id);
            $delete->execute();
            $msg = 'User berhasil dihapus.';
        }
    }
}

$rows = $conn->query(
    'SELECT id, nama, username, level
     FROM user
     ORDER BY id DESC'
);

$pageTitle = 'Data User';
$pageDescription = 'Kelola akun administrator dan user perpustakaan.';
require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($msg): ?>
    <div class="alert <?= e($type) ?>"><?= e($msg) ?></div>
<?php endif; ?>

<section class="page-intro">
    <div>
        <span class="page-label">DATA MASTER</span>
        <h1>Data User</h1>
        <p>Kelola akun yang dapat mengakses sistem perpustakaan.</p>
    </div>
    <a href="<?= e(url('user/form.php')) ?>" class="btn primary">＋ Tambah User</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows->num_rows): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Nama"><b><?= e($row['nama']) ?></b></td>
                        <td data-label="Username"><?= e($row['username']) ?></td>
                        <td data-label="Level">
                            <span class="badge <?= $row['level'] === 'admin' ? 'blue' : 'green' ?>">
                                <?= e(ucfirst($row['level'])) ?>
                            </span>
                        </td>
                        <td data-label="Aksi" class="actions-cell">
                            <a class="action edit" href="<?= e(url('user/form.php')) ?>?id=<?= (int) $row['id'] ?>">Edit</a>
                            <?php if ((int) $row['id'] !== (int) $_SESSION['user_id']): ?>
                                <form method="post" class="inline-form" onsubmit="return confirm('Hapus user ini?')">
                                    <input type="hidden" name="hapus_id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" class="action delete">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty">Belum ada user.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
