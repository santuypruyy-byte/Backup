<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$msg = '';
$type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $id = (int) $_POST['hapus_id'];

    $check = $conn->prepare('SELECT COUNT(*) AS total FROM buku WHERE penerbit_id = ?');
    $check->bind_param('i', $id);
    $check->execute();
    $total = (int) $check->get_result()->fetch_assoc()['total'];

    if ($total > 0) {
        $msg = 'Penerbit tidak dapat dihapus karena masih digunakan oleh buku.';
        $type = 'danger';
    } else {
        $delete = $conn->prepare('DELETE FROM penerbit WHERE id = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
        $msg = 'Penerbit berhasil dihapus.';
    }
}

$rows = $conn->query(
    'SELECT p.id, p.kode_penerbit, p.nama_penerbit,
            COUNT(b.id) AS jumlah_buku
     FROM penerbit p
     LEFT JOIN buku b ON b.penerbit_id = p.id
     GROUP BY p.id, p.kode_penerbit, p.nama_penerbit
     ORDER BY p.id DESC'
);

$pageTitle = 'Data Penerbit';
$pageDescription = 'Kelola data penerbit buku perpustakaan.';
require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($msg): ?>
    <div class="alert <?= e($type) ?>"><?= e($msg) ?></div>
<?php endif; ?>

<section class="page-intro">
    <div>
        <span class="page-label">DATA MASTER</span>
        <h1>Data Penerbit</h1>
        <p>Kelola penerbit yang digunakan pada koleksi buku.</p>
    </div>
    <a href="<?= e(url('penerbit/form.php')) ?>" class="btn primary">＋ Tambah Penerbit</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Penerbit</th>
                    <th>Jumlah Buku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows->num_rows): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Kode"><span class="book-code"><?= e($row['kode_penerbit']) ?></span></td>
                        <td data-label="Nama Penerbit"><b><?= e($row['nama_penerbit']) ?></b></td>
                        <td data-label="Jumlah Buku"><?= (int) $row['jumlah_buku'] ?> buku</td>
                        <td data-label="Aksi" class="actions-cell">
                            <a class="action edit" href="<?= e(url('penerbit/form.php')) ?>?id=<?= (int) $row['id'] ?>">Edit</a>
                            <form method="post" class="inline-form" onsubmit="return confirm('Hapus penerbit ini?')">
                                <input type="hidden" name="hapus_id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="action delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty">Belum ada penerbit.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
