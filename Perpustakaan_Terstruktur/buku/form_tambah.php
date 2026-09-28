<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';

$kategori = $conn->query(
    'SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori'
);

$penerbit = $conn->query(
    'SELECT id, nama_penerbit FROM penerbit ORDER BY nama_penerbit'
);

$pageTitle = 'Tambah Buku';
$pageDescription = 'Tambahkan koleksi baru ke perpustakaan.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<section class="panel form-panel">
    <div class="form-title">
        <span class="page-label">DATA BUKU</span>
        <h1>Tambah Buku</h1>
        <p>Lengkapi informasi buku di bawah ini.</p>
    </div>

    <form
        action="<?= e(url('buku/tambah.php')) ?>"
        method="post"
        enctype="multipart/form-data"
        class="form-grid"
    >
        <div>
            <label>Judul Buku</label>
            <input
                type="text"
                name="judul"
                placeholder="Masukkan judul buku"
                required
            >
        </div>

        <div>
            <label>Kategori</label>
            <select name="kategori_id" required>
                <option value="">Pilih kategori</option>

                <?php while ($row = $kategori->fetch_assoc()): ?>
                    <option value="<?= (int) $row['id'] ?>">
                        <?= e($row['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Penerbit</label>
            <select name="penerbit_id" required>
                <option value="">Pilih penerbit</option>

                <?php while ($row = $penerbit->fetch_assoc()): ?>
                    <option value="<?= (int) $row['id'] ?>">
                        <?= e($row['nama_penerbit']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Pengarang</label>
            <input
                type="text"
                name="pengarang"
                placeholder="Nama pengarang"
            >
        </div>

        <div>
            <label>Jumlah Halaman</label>
            <input
                type="number"
                name="jumlah_halaman"
                min="1"
                placeholder="Contoh: 300"
            >
        </div>

        <div>
            <label>Jumlah Stok</label>
            <input
                type="number"
                name="jumlah_stok"
                min="0"
                value="1"
                required
            >
        </div>

        <div>
            <label>Tahun Terbit</label>
            <input
                type="number"
                name="tahun_terbit"
                min="1900"
                max="2100"
                placeholder="2026"
            >
        </div>

        <div>
            <label>Gambar Buku</label>
            <input
                type="file"
                name="gambar"
                accept="image/jpeg,image/png,image/webp"
                required
            >
            <small class="file-help">
                JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
            </small>
        </div>

        <div class="full">
            <label>Sinopsis</label>
            <textarea
                name="sinopsis"
                rows="6"
                placeholder="Masukkan sinopsis buku"
            ></textarea>
        </div>

        <div class="full form-actions">
            <button type="submit" class="btn primary">
                Simpan Buku
            </button>

            <a
                href="<?= e(url('buku/tampil.php')) ?>"
                class="btn"
            >
                Batal
            </a>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
