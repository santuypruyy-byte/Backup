<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
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

$oldStmt = $conn->prepare(
    'SELECT gambar
     FROM buku
     WHERE id = ? AND status_aktif = 1'
);

$oldStmt->bind_param('i', $id);
$oldStmt->execute();

$old = $oldStmt->get_result()->fetch_assoc();

if (!$old) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

$oldGambar = $old['gambar'] ?? '';

$upload = uploadGambarBuku('gambar', false);

if (!$upload['success']) {
    die(
        e($upload['error']) .
        '<br><br><a href="' .
        e(url('buku/edit.php')) .
        '?id=' . $id .
        '">Kembali</a>'
    );
}

$gambar = $upload['filename'] ?: $oldGambar;

$sql = 'UPDATE buku SET
            judul = ?,
            kategori_id = ?,
            penerbit_id = ?,
            pengarang = ?,
            jumlah_halaman = ?,
            jumlah_stok = ?,
            tahun_terbit = ?,
            sinopsis = ?,
            gambar = ?
        WHERE id = ? AND status_aktif = 1';

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'siisiiissi',
    $judul,
    $kategoriId,
    $penerbitId,
    $pengarang,
    $jumlahHalaman,
    $jumlahStok,
    $tahunTerbit,
    $sinopsis,
    $gambar,
    $id
);

if ($stmt->execute()) {
    if (
        $upload['filename'] &&
        $oldGambar &&
        $oldGambar !== $gambar
    ) {
        $oldPath = __DIR__ . '/../assets/images/' . basename($oldGambar);

        if (
            is_file($oldPath) &&
            basename($oldGambar) !== 'default.svg'
        ) {
            @unlink($oldPath);
        }
    }

    header('Location: ' . url('buku/tampil.php'));
    exit;
}

if (!empty($upload['path']) && is_file($upload['path'])) {
    @unlink($upload['path']);
}

die('Data gagal diubah: ' . e($stmt->error));
