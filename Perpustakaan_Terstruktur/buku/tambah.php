<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('buku/form_tambah.php'));
    exit;
}

$judul = trim($_POST['judul'] ?? '');
$kategoriId = (int) ($_POST['kategori_id'] ?? 0);
$penerbitId = (int) ($_POST['penerbit_id'] ?? 0);
$pengarang = trim($_POST['pengarang'] ?? '');

$jumlahHalaman = ($_POST['jumlah_halaman'] ?? '') !== ''
    ? (int) $_POST['jumlah_halaman']
    : null;

$jumlahStok = (int) ($_POST['jumlah_stok'] ?? 0);

$tahunTerbit = ($_POST['tahun_terbit'] ?? '') !== ''
    ? (int) $_POST['tahun_terbit']
    : null;

$sinopsis = trim($_POST['sinopsis'] ?? '');

if ($judul === '' || $kategoriId < 1 || $penerbitId < 1 || $jumlahStok < 0) {
    die('Data buku belum lengkap.');
}

$upload = uploadGambarBuku('gambar', true);

if (!$upload['success']) {
    die(
        e($upload['error']) .
        '<br><br><a href="' .
        e(url('buku/form_tambah.php')) .
        '">Kembali</a>'
    );
}

$gambar = $upload['filename'];

$sql = 'INSERT INTO buku (
            judul,
            kategori_id,
            penerbit_id,
            pengarang,
            jumlah_halaman,
            jumlah_stok,
            tahun_terbit,
            sinopsis,
            gambar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'siisiiiss',
    $judul,
    $kategoriId,
    $penerbitId,
    $pengarang,
    $jumlahHalaman,
    $jumlahStok,
    $tahunTerbit,
    $sinopsis,
    $gambar
);

if ($stmt->execute()) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

if (!empty($upload['path']) && is_file($upload['path'])) {
    @unlink($upload['path']);
}

die('Data gagal ditambahkan: ' . e($stmt->error));
