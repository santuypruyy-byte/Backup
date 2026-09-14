<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/connection.php";
$buku = (int) $conn
  ->query("SELECT COUNT(*) total FROM buku WHERE status_aktif=1")
  ->fetch_assoc()["total"];
$anggota = (int) $conn
  ->query("SELECT COUNT(*) total FROM anggota")
  ->fetch_assoc()["total"];
$pinjam = (int) $conn
  ->query("SELECT COUNT(*) total FROM peminjaman WHERE status='dipinjam'")
  ->fetch_assoc()["total"];
$kembali = (int) $conn
  ->query(
    "SELECT COUNT(*) total FROM peminjaman WHERE status='sudah dikembalikan'"
  )
  ->fetch_assoc()["total"];
$stok = (int) $conn
  ->query(
    "SELECT COALESCE(SUM(jumlah_stok),0) total FROM buku WHERE status_aktif=1"
  )
  ->fetch_assoc()["total"];
$pageTitle = "Dashboard";
$pageDescription = "Ringkasan aktivitas perpustakaan.";
require_once __DIR__ . "/../includes/layout_header.php";
?>
<section class="hero-panel">
    <div><span class="page-label">SISTEM INFORMASI PERPUSTAKAAN</span><h1>Selamat datang, <?= e(
      $_SESSION["nama"]
    ) ?> 👋</h1><p>Kelola koleksi buku dan transaksi perpustakaan dari satu tempat.</p></div>
    <div class="hero-icon">📚</div>
</section>
<section class="stats-grid">
    <div class="stat-card"><div class="stat-icon">📚</div><div><span>Total Buku</span><strong><?= $buku ?></strong><small><?= $stok ?> total stok</small></div></div>
    <div class="stat-card"><div class="stat-icon blue">👥</div><div><span>Total Anggota</span><strong><?= $anggota ?></strong><small>anggota terdaftar</small></div></div>
    <div class="stat-card"><div class="stat-icon orange">↗</div><div><span>Sedang Dipinjam</span><strong><?= $pinjam ?></strong><small>transaksi aktif</small></div></div>
    <div class="stat-card"><div class="stat-icon green">✓</div><div><span>Sudah Kembali</span><strong><?= $kembali ?></strong><small>transaksi selesai</small></div></div>
</section>
<section class="quick-grid">
    <div class="panel"><div class="panel-head"><div><h3>Akses Cepat</h3><p class="muted">Menu yang sering digunakan.</p></div></div><div class="quick-actions">
        <?php if ($_SESSION["level"] === "admin"): ?><a href="<?= e(
  url("buku/tampil.php")
) ?>" class="quick-action"><span>📚</span><div><b>Data Buku</b><small>Kelola koleksi buku</small></div><strong>→</strong></a><a href="<?= e(
  url("anggota/anggota.php")
) ?>" class="quick-action"><span>👥</span><div><b>Data Anggota</b><small>Kelola anggota</small></div><strong>→</strong></a><a href="<?= e(url("kategori/index.php")) ?>" class="quick-action"><span>🏷️</span><div><b>Data Kategori</b><small>Kelola kategori buku</small></div><strong>→</strong></a><a href="<?= e(url("penerbit/index.php")) ?>" class="quick-action"><span>🏢</span><div><b>Data Penerbit</b><small>Kelola penerbit buku</small></div><strong>→</strong></a><a href="<?= e(url("user/index.php")) ?>" class="quick-action"><span>👤</span><div><b>Data User</b><small>Kelola akun sistem</small></div><strong>→</strong></a><?php endif; ?>
        <a href="<?= e(
          url("transaksi/peminjaman.php")
        ) ?>" class="quick-action"><span>📖</span><div><b>Peminjaman</b><small>Catat buku dipinjam</small></div><strong>→</strong></a>
        <?php if ($_SESSION["level"] === "admin"): ?>
            <a href="<?= e(url("transaksi/pengembalian.php")) ?>" class="quick-action"><span>↙</span><div><b>Pengembalian</b><small>Proses pengembalian buku</small></div><strong>→</strong></a>
        <?php else: ?>
            <a href="<?= e(url("transaksi/riwayat_buku.php")) ?>" class="quick-action"><span>↙</span><div><b>Riwayat Buku</b><small>Lihat status buku saya</small></div><strong>→</strong></a>
        <?php endif; ?>
        <a href="<?= e(
  url("pencarian/cari_buku.php")
) ?>" class="quick-action"><span>🔎</span><div><b>Cari Buku</b><small>Temukan buku</small></div><strong>→</strong></a>
    </div></div>
    <div class="panel role-panel"><span class="role-badge"><?= e(
      ucfirst($_SESSION["level"])
    ) ?></span><h3><?= $_SESSION["level"] === "admin"
  ? "Panel Administrator"
  : "Panel User" ?></h3><p><?= $_SESSION["level"] === "admin"
  ? "Kamu memiliki akses untuk mengelola buku, anggota, peminjaman, dan pengembalian."
  : "Kamu dapat melakukan peminjaman, melihat riwayat buku, dan mencari buku." ?></p></div>
</section>
<?php require_once __DIR__ . "/../includes/layout_footer.php"; ?>
