<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<div class="brand side-brand">
    <div class="brand-icon">📚</div>
    <div>
        <b>Perpustakaan</b>
        <small><?= e(ucfirst($_SESSION['level'])) ?></small>
    </div>
</div>
<nav class="sidebar-nav">
    <div class="nav-label">MENU UTAMA</div>
    <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= e(url('dashboard/dashboard.php')) ?>"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
    <?php if ($_SESSION['level'] === 'admin'): ?>
        <a class="<?= in_array($currentPage, ['tampil.php','form_tambah.php','edit.php','detail.php'], true) ? 'active' : '' ?>" href="<?= e(url('buku/tampil.php')) ?>"><span class="nav-icon">▣</span><span>Data Buku</span></a>
        <a class="<?= in_array($currentPage, ['anggota.php','anggota_form.php'], true) ? 'active' : '' ?>" href="<?= e(url('anggota/anggota.php')) ?>"><span class="nav-icon">♙</span><span>Data Anggota</span></a>
        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/kategori/') ? 'active' : '' ?>" href="<?= e(url('kategori/index.php')) ?>"><span class="nav-icon">▤</span><span>Data Kategori</span></a>
        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/penerbit/') ? 'active' : '' ?>" href="<?= e(url('penerbit/index.php')) ?>"><span class="nav-icon">▥</span><span>Data Penerbit</span></a>
        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/user/') ? 'active' : '' ?>" href="<?= e(url('user/index.php')) ?>"><span class="nav-icon">♙</span><span>Data User</span></a>
    <?php endif; ?>
    <a class="<?= $currentPage === 'peminjaman.php' ? 'active' : '' ?>" href="<?= e(url('transaksi/peminjaman.php')) ?>"><span class="nav-icon">↗</span><span>Peminjaman</span></a>
    <?php if ($_SESSION['level'] === 'admin'): ?>
        <a class="<?= $currentPage === 'pengembalian.php' ? 'active' : '' ?>" href="<?= e(url('transaksi/pengembalian.php')) ?>"><span class="nav-icon">↙</span><span>Pengembalian</span></a>
    <?php else: ?>
        <a class="<?= $currentPage === 'riwayat_buku.php' ? 'active' : '' ?>" href="<?= e(url('transaksi/riwayat_buku.php')) ?>"><span class="nav-icon">↙</span><span>Riwayat Buku</span></a>
    <?php endif; ?>
    <a class="<?= $currentPage === 'cari_buku.php' ? 'active' : '' ?>" href="<?= e(url('pencarian/cari_buku.php')) ?>"><span class="nav-icon">⌕</span><span>Cari Buku</span></a>
    <div class="nav-label nav-label-bottom">AKUN</div>
    <a href="<?= e(url('auth/logout.php')) ?>" class="logout"><span class="nav-icon">⇥</span><span>Logout</span></a>
</nav>
