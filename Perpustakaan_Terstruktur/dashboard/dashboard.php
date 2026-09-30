<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../config/connection.php';

$pageTitle = 'Dashboard';
$pageDescription = 'Informasi dan pencarian koleksi perpustakaan';

/*
|--------------------------------------------------------------------------
| FILTER OPAC
|--------------------------------------------------------------------------
*/

$keyword    = trim($_GET['q'] ?? '');
$kategoriId = (int) ($_GET['kategori'] ?? 0);
$penerbitId = (int) ($_GET['penerbit'] ?? 0);
$tahun      = (int) ($_GET['tahun'] ?? 0);
$statusBuku = $_GET['status'] ?? '';
$currentUserId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| DATA FILTER
|--------------------------------------------------------------------------
*/

$kategoriResult = $conn->query("
    SELECT id, nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC
");

if (!$kategoriResult) {
    die("Gagal mengambil kategori: " . $conn->error);
}

$penerbitResult = $conn->query("
    SELECT id, nama_penerbit
    FROM penerbit
    ORDER BY nama_penerbit ASC
");

if (!$penerbitResult) {
    die("Gagal mengambil penerbit: " . $conn->error);
}

$tahunResult = $conn->query("
    SELECT DISTINCT tahun_terbit
    FROM buku
    WHERE tahun_terbit IS NOT NULL
      AND tahun_terbit > 0
      AND status_aktif = 1
    ORDER BY tahun_terbit DESC
");

if (!$tahunResult) {
    die("Gagal mengambil tahun buku: " . $conn->error);
}

/*
|--------------------------------------------------------------------------
| STATISTIK DASHBOARD
|--------------------------------------------------------------------------
*/

function getCount(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int) ($row['total'] ?? 0);
}

$totalBuku = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM buku
     WHERE status_aktif = 1"
);

$totalAnggota = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM anggota"
);

$totalKategori = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM kategori"
);

$totalDipinjam = getCount(
    $conn,
    "SELECT COALESCE(SUM(pd.jumlah), 0) AS total
     FROM peminjaman_detail pd
     INNER JOIN peminjaman p
        ON p.id = pd.peminjaman_id
     INNER JOIN buku b
        ON b.id = pd.buku_id
     WHERE p.status = 'dipinjam'
       AND b.status_aktif = 1"
);

/*
|--------------------------------------------------------------------------
| QUERY BUKU
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        b.id,
        b.judul,
        b.pengarang,
        b.jumlah_halaman,
        b.jumlah_stok,
        b.tahun_terbit,
        b.sinopsis,
        b.gambar,
        b.status_aktif,

        k.nama_kategori,

        p.nama_penerbit,

        COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM rating_buku r WHERE r.buku_id = b.id), 0) AS rating_rata,
        COALESCE((SELECT COUNT(*) FROM rating_buku r WHERE r.buku_id = b.id), 0) AS jumlah_rating,
        EXISTS(SELECT 1 FROM bookmark bm WHERE bm.buku_id = b.id AND bm.user_id = $currentUserId) AS is_bookmarked,

        COALESCE(
            (
                SELECT SUM(pd.jumlah)
                FROM peminjaman_detail pd
                INNER JOIN peminjaman pm
                    ON pm.id = pd.peminjaman_id
                WHERE pd.buku_id = b.id
                  AND pm.status = 'dipinjam'
            ),
            0
        ) AS sedang_dipinjam

    FROM buku b

    LEFT JOIN kategori k
        ON k.id = b.kategori_id

    LEFT JOIN penerbit p
        ON p.id = b.penerbit_id

    WHERE b.status_aktif = 1
";

$params = [];
$types  = "";

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($keyword !== '') {

    $sql .= "
        AND (
            b.judul LIKE ?
            OR b.pengarang LIKE ?
            OR b.sinopsis LIKE ?
        )
    ";

    $search = '%' . $keyword . '%';

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "sss";
}

/*
|--------------------------------------------------------------------------
| FILTER KATEGORI
|--------------------------------------------------------------------------
*/

if ($kategoriId > 0) {

    $sql .= " AND b.kategori_id = ? ";

    $params[] = $kategoriId;
    $types .= "i";
}

/*
|--------------------------------------------------------------------------
| FILTER PENERBIT
|--------------------------------------------------------------------------
*/

if ($penerbitId > 0) {

    $sql .= " AND b.penerbit_id = ? ";

    $params[] = $penerbitId;
    $types .= "i";
}

/*
|--------------------------------------------------------------------------
| FILTER TAHUN
|--------------------------------------------------------------------------
*/

if ($tahun > 0) {

    $sql .= " AND b.tahun_terbit = ? ";

    $params[] = $tahun;
    $types .= "i";
}

/*
|--------------------------------------------------------------------------
| FILTER STATUS
|--------------------------------------------------------------------------
*/

if ($statusBuku === 'tersedia') {

    $sql .= "
        AND b.jumlah_stok >
        (
            SELECT COALESCE(SUM(pd.jumlah), 0)
            FROM peminjaman_detail pd
            INNER JOIN peminjaman pm
                ON pm.id = pd.peminjaman_id
            WHERE pd.buku_id = b.id
              AND pm.status = 'dipinjam'
        )
    ";

} elseif ($statusBuku === 'habis') {

    $sql .= "
        AND b.jumlah_stok <=
        (
            SELECT COALESCE(SUM(pd.jumlah), 0)
            FROM peminjaman_detail pd
            INNER JOIN peminjaman pm
                ON pm.id = pd.peminjaman_id
            WHERE pd.buku_id = b.id
              AND pm.status = 'dipinjam'
        )
    ";
}

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY b.judul ASC ";

/*
|--------------------------------------------------------------------------
| EKSEKUSI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query buku gagal: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Eksekusi query buku gagal: " . $stmt->error);
}

$bookResult = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| JUMLAH HASIL
|--------------------------------------------------------------------------
*/

$totalHasil = $bookResult->num_rows;

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/layout_header.php';

?>

<!-- =====================================================
     DASHBOARD HERO
====================================================== -->

<section class="hero-panel">

    <div>

        <span class="page-label">
            SISTEM INFORMASI PERPUSTAKAAN
        </span>

        <h1>
            Halo, <?= e($_SESSION['nama'] ?? 'Pengguna') ?> 👋
        </h1>

        <p>
            Kelola koleksi buku dan cari buku yang kamu butuhkan
            melalui sistem perpustakaan.
        </p>

    </div>

    <div class="hero-icon">
        📚
    </div>

</section>


<!-- =====================================================
     STATISTIK
====================================================== -->

<section class="stats-grid">

    <div class="stat-card">

        <div class="stat-icon blue">
            📚
        </div>

        <div>
            <span>Total Buku</span>
            <strong><?= $totalBuku ?></strong>
            <small>Koleksi aktif</small>
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon green">
            👥
        </div>

        <div>
            <span>Total Anggota</span>
            <strong><?= $totalAnggota ?></strong>
            <small>Anggota terdaftar</small>
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon orange">
            📖
        </div>

        <div>
            <span>Sedang Dipinjam</span>
            <strong><?= $totalDipinjam ?></strong>
            <small>Eksemplar dipinjam</small>
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon">
            🗂️
        </div>

        <div>
            <span>Kategori</span>
            <strong><?= $totalKategori ?></strong>
            <small>Kategori buku</small>
        </div>

    </div>

</section>


<!-- =====================================================
     QUICK ACTION
====================================================== -->

<section class="quick-grid">

    <div class="panel">

        <div class="form-title">

            <h3>Akses Cepat</h3>

            <p>
                Menu yang sering digunakan.
            </p>

        </div>

        <div class="quick-actions">

            <?php if (($_SESSION['level'] ?? '') === 'admin'): ?>

                <a
                    class="quick-action"
                    href="<?= e(url('buku/tampil.php')) ?>"
                >
                    <span>📚</span>

                    <div>
                        <b>Kelola Buku</b>
                        <small>Tambah dan kelola koleksi</small>
                    </div>

                    <strong>›</strong>
                </a>


                <a
                    class="quick-action"
                    href="<?= e(url('anggota/anggota.php')) ?>"
                >
                    <span>👥</span>

                    <div>
                        <b>Data Anggota</b>
                        <small>Kelola anggota perpustakaan</small>
                    </div>

                    <strong>›</strong>
                </a>

            <?php endif; ?>


            <a
                class="quick-action"
                href="<?= e(url('transaksi/peminjaman.php')) ?>"
            >
                <span>📖</span>

                <div>
                    <b>Peminjaman</b>
                    <small>Proses peminjaman buku</small>
                </div>

                <strong>›</strong>
            </a>


            <?php if (($_SESSION['level'] ?? '') === 'admin'): ?>

                <a
                    class="quick-action"
                    href="<?= e(url('transaksi/pengembalian.php')) ?>"
                >
                    <span>↩️</span>

                    <div>
                        <b>Pengembalian</b>
                        <small>Kelola buku yang dikembalikan</small>
                    </div>

                    <strong>›</strong>
                </a>

            <?php else: ?>

                <a
                    class="quick-action"
                    href="<?= e(url('transaksi/riwayat_buku.php')) ?>"
                >
                    <span>📋</span>

                    <div>
                        <b>Riwayat Buku</b>
                        <small>Lihat riwayat peminjaman</small>
                    </div>

                    <strong>›</strong>
                </a>

            <?php endif; ?>


            <a
                class="quick-action"
                href="<?= e(url('pencarian/cari_buku.php')) ?>"
            >
                <span>🔎</span>

                <div>
                    <b>Cari Buku</b>
                    <small>Cari koleksi perpustakaan</small>
                </div>

                <strong>›</strong>
            </a>

        </div>

    </div>


    <div class="panel role-panel">

        <span class="role-badge">
            <?= e(strtoupper($_SESSION['level'] ?? 'USER')) ?>
        </span>

        <h3>
            Akses Akun
        </h3>

        <?php if (($_SESSION['level'] ?? '') === 'admin'): ?>

            <p>
                Kamu login sebagai administrator.
                Kamu dapat mengelola buku, anggota, kategori,
                penerbit, user, peminjaman, dan pengembalian.
            </p>

        <?php else: ?>

            <p>
                Kamu login sebagai anggota/user.
                Kamu dapat mencari buku, melakukan peminjaman,
                dan melihat riwayat peminjaman sendiri.
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     OPAC
====================================================== -->

<section class="search-hero">

    <div>

        <span class="page-label">
            OPAC
        </span>

        <h1>
            Cari Buku
        </h1>

        <p>
            Cari berdasarkan judul, pengarang, kategori,
            penerbit, tahun, atau status ketersediaan.
        </p>

    </div>


    <div class="search-big">

        <form method="GET">

            <input
                type="text"
                name="q"
                value="<?= e($keyword) ?>"
                placeholder="Cari judul, pengarang, atau sinopsis..."
            >


            <select name="kategori">

                <option value="0">
                    Semua Kategori
                </option>

                <?php while ($kategori = $kategoriResult->fetch_assoc()): ?>

                    <option
                        value="<?= (int) $kategori['id'] ?>"
                        <?= $kategoriId === (int) $kategori['id'] ? 'selected' : '' ?>
                    >
                        <?= e($kategori['nama_kategori']) ?>
                    </option>

                <?php endwhile; ?>

            </select>


            <select name="penerbit">

                <option value="0">
                    Semua Penerbit
                </option>

                <?php while ($penerbit = $penerbitResult->fetch_assoc()): ?>

                    <option
                        value="<?= (int) $penerbit['id'] ?>"
                        <?= $penerbitId === (int) $penerbit['id'] ? 'selected' : '' ?>
                    >
                        <?= e($penerbit['nama_penerbit']) ?>
                    </option>

                <?php endwhile; ?>

            </select>


            <select name="tahun">

                <option value="0">
                    Semua Tahun
                </option>

                <?php while ($tahunData = $tahunResult->fetch_assoc()): ?>

                    <?php $tahunValue = (int) $tahunData['tahun_terbit']; ?>

                    <option
                        value="<?= $tahunValue ?>"
                        <?= $tahun === $tahunValue ? 'selected' : '' ?>
                    >
                        <?= $tahunValue ?>
                    </option>

                <?php endwhile; ?>

            </select>


            <select name="status">

                <option value="">
                    Semua Status
                </option>

                <option
                    value="tersedia"
                    <?= $statusBuku === 'tersedia' ? 'selected' : '' ?>
                >
                    Tersedia
                </option>

                <option
                    value="habis"
                    <?= $statusBuku === 'habis' ? 'selected' : '' ?>
                >
                    Habis
                </option>

            </select>


            <button
                type="submit"
                class="btn primary"
            >
                🔎 Cari
            </button>


            <a
                href="<?= e(url('dashboard/dashboard.php')) ?>"
                class="btn"
            >
                Reset
            </a>

        </form>

    </div>

</section>


<!-- =====================================================
     HASIL BUKU
====================================================== -->

<section class="panel">

    <div class="page-intro">

        <div>

            <span class="page-label">
                KOLEKSI
            </span>

            <h1>
                Daftar Buku
            </h1>

            <p>
                Menampilkan <?= $totalHasil ?> buku.
            </p>

        </div>

    </div>


    <div class="book-grid">

        <?php if ($totalHasil === 0): ?>

            <div class="empty-card">

                <div>📚</div>

                <h3>
                    Buku tidak ditemukan
                </h3>

                <p>
                    Coba gunakan kata kunci atau filter yang berbeda.
                </p>

                <a
                    href="<?= e(url('dashboard/dashboard.php')) ?>"
                    class="btn primary"
                >
                    Reset Pencarian
                </a>

            </div>

        <?php else: ?>


            <?php while ($book = $bookResult->fetch_assoc()): ?>

                <?php

                $stok = (int) ($book['jumlah_stok'] ?? 0);

                $dipinjam = (int) ($book['sedang_dipinjam'] ?? 0);

                $tersedia = max(0, $stok - $dipinjam);

                $gambar = trim((string) ($book['gambar'] ?? ''));

                if ($gambar !== '') {

                    $gambarUrl = url('assets/images/' . $gambar);

                } else {

                    $gambarUrl = url('assets/images/default.svg');
                }

                ?>


                <article class="book-card">

                    <div class="book-cover">

                        <img
                            src="<?= e($gambarUrl) ?>"
                            alt="<?= e($book['judul']) ?>"
                            onerror="this.onerror=null;this.src='<?= e(url('assets/images/default.svg')) ?>';"
                        >


                        <?php if ($tersedia > 0): ?>

                            <span class="availability available">
                                Tersedia
                            </span>

                        <?php else: ?>

                            <span class="availability unavailable">
                                Habis
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="book-content">

                        <?php if (!empty($book['nama_kategori'])): ?>

                            <span class="book-category">
                                <?= e($book['nama_kategori']) ?>
                            </span>

                        <?php endif; ?>


                        <h3>
                            <?= e($book['judul']) ?>
                        </h3>


                        <p class="book-author">

                            <?= e($book['pengarang'] ?: 'Pengarang tidak diketahui') ?>

                        </p>

                        <p class="rating-summary">
                            ⭐ <?= e($book['rating_rata']) ?>/5
                            <span>(<?= (int) $book['jumlah_rating'] ?> rating)</span>
                        </p>


                        <p class="book-description">

                            <?= e(
                                $book['sinopsis']
                                ?: 'Belum ada sinopsis untuk buku ini.'
                            ) ?>

                        </p>


                        <div class="book-meta">

                            <span>
                                Tahun:
                                <?= e($book['tahun_terbit'] ?: '-') ?>
                            </span>

                            <span>
                                Stok:
                                <?= $tersedia ?>
                            </span>

                        </div>


                        <div class="book-actions book-actions-main">

                            <a href="<?= e(url('buku/detail.php?id=' . (int) $book['id'])) ?>" class="btn small">Detail</a>

                            <?php if ($tersedia > 0): ?>
                                <a href="<?= e(url('transaksi/peminjaman.php?buku_id=' . (int) $book['id'])) ?>" class="btn small primary">📖 Pinjam</a>
                            <?php else: ?>
                                <span class="btn small">Habis</span>
                            <?php endif; ?>

                            <form method="post" action="<?= e(url('aksi/bookmark.php')) ?>">
                                <input type="hidden" name="buku_id" value="<?= (int) $book['id'] ?>">
                                <input type="hidden" name="return" value="dashboard/dashboard.php">
                                <button type="submit" class="btn small <?= !empty($book['is_bookmarked']) ? 'bookmark-active' : '' ?>">
                                    <?= !empty($book['is_bookmarked']) ? '✓ Tersimpan' : '🔖 Simpan' ?>
                                </button>
                            </form>


                            <?php if (($_SESSION['level'] ?? '') === 'admin'): ?>

                                <a
                                    href="<?= e(url('buku/edit.php?id=' . (int) $book['id'])) ?>"
                                    class="btn small"
                                >
                                    Edit
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </article>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</section>


<?php

require_once __DIR__ . '/../includes/layout_footer.php';
?>