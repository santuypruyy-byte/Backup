<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';

$sql = "SELECT
            buku.*,
            kategori.nama_kategori,
            penerbit.nama_penerbit,
            COALESCE(
                SUM(
                    CASE
                        WHEN peminjaman.status = 'dipinjam'
                        THEN peminjaman_detail.jumlah
                        ELSE 0
                    END
                ),
                0
            ) AS jumlah_dipinjam
        FROM buku
        LEFT JOIN kategori
            ON buku.kategori_id = kategori.id
        LEFT JOIN penerbit
            ON buku.penerbit_id = penerbit.id
        LEFT JOIN peminjaman_detail
            ON buku.id = peminjaman_detail.buku_id
        LEFT JOIN peminjaman
            ON peminjaman_detail.peminjaman_id = peminjaman.id
        WHERE buku.status_aktif = 1
        GROUP BY buku.id
        ORDER BY buku.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die('Query gagal: ' . e($conn->error));
}

$total = $result->num_rows;

$pageTitle = 'Data Buku';
$pageDescription = 'Kelola koleksi buku perpustakaan.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<section class="page-intro">
    <div>
        <span class="page-label">KOLEKSI</span>
        <h1>Data Buku</h1>
        <p>
            Daftar koleksi dengan informasi utama dan ketersediaan stok.
        </p>
    </div>

    <a
        href="<?= e(url('buku/form_tambah.php')) ?>"
        class="btn primary"
    >
        ＋ Tambah Buku
    </a>
</section>

<div class="summary-row">
    <div class="summary-card">
        <span>📚</span>

        <div>
            <small>Total buku</small>
            <strong><?= $total ?></strong>
        </div>
    </div>
</div>

<section class="book-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $stok = (int) $row['jumlah_stok'];
        $dipinjam = (int) $row['jumlah_dipinjam'];
        $tersedia = max(0, $stok - $dipinjam);

        $gambar = !empty($row['gambar'])
            ? url('assets/images/' . $row['gambar'])
            : url('assets/images/default.svg');

        $sinopsis = $row['sinopsis'] ?? 'Belum ada sinopsis.';

        if (strlen($sinopsis) > 120) {
            $sinopsis = substr($sinopsis, 0, 120) . '...';
        }
        ?>

        <article class="book-card">
            <div class="book-cover">
                <img
                    src="<?= e($gambar) ?>"
                    alt="<?= e($row['judul']) ?>"
                    onerror="this.src='<?= e(url('assets/images/default.svg')) ?>'"
                >

                <span
                    class="availability <?= $tersedia > 0 ? 'available' : 'unavailable' ?>"
                >
                    <?= $tersedia > 0 ? 'Tersedia' : 'Habis' ?>
                </span>
            </div>

            <div class="book-content">
                <span class="book-category">
                    <?= e($row['nama_kategori'] ?? 'Umum') ?>
                </span>

                <h3><?= e($row['judul']) ?></h3>

                <p class="book-author">
                    ✍ <?= e($row['pengarang'] ?? '-') ?>
                </p>

                <p class="book-description">
                    <?= e($sinopsis) ?>
                </p>

                <div class="book-meta">
                    <span>
                        📅 <?= e($row['tahun_terbit'] ?? '-') ?>
                    </span>

                    <span>
                        📦 <?= $tersedia ?>/<?= $stok ?>
                    </span>
                </div>

                <div class="book-actions">
                    <a
                        class="btn primary"
                        href="<?= e(url('buku/detail.php')) ?>?id=<?= (int) $row['id'] ?>"
                    >
                        Detail
                    </a>

                    <a
                        class="btn"
                        href="<?= e(url('buku/edit.php')) ?>?id=<?= (int) $row['id'] ?>"
                    >
                        Edit
                    </a>

                    <a
                        class="btn danger"
                        href="<?= e(url('buku/hapus.php')) ?>?id=<?= (int) $row['id'] ?>"
                        onclick="return confirm('Buku akan diarsipkan. Riwayat peminjaman tetap disimpan. Lanjutkan?')"
                    >
                        Hapus
                    </a>
                </div>
            </div>
        </article>
    <?php endwhile; ?>

    <?php if ($total === 0): ?>
        <div class="empty-card">
            <div>📚</div>
            <h3>Belum ada buku</h3>
            <p>Tambahkan koleksi buku terlebih dahulu.</p>

            <a
                href="<?= e(url('buku/form_tambah.php')) ?>"
                class="btn primary"
            >
                Tambah Buku
            </a>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
