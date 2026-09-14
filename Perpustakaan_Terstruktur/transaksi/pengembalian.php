<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
requireAdmin();

require_once __DIR__ . '/../config/connection.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peminjamanId = (int) ($_POST['peminjaman_id'] ?? 0);

    if ($peminjamanId < 1) {
        $err = 'Transaksi pengembalian tidak valid.';
    } else {
        $conn->begin_transaction();

        try {
            /*
             * Ambil transaksi yang masih dipinjam.
             * FOR UPDATE mencegah transaksi yang sama diproses bersamaan.
             */
            $stmt = $conn->prepare(
                "SELECT
                    p.id,
                    p.tanggal_pinjam,
                    p.lama_pinjam,
                    COALESCE(SUM(pd.jumlah), 0) AS jumlah_buku
                 FROM peminjaman p
                 JOIN peminjaman_detail pd
                    ON pd.peminjaman_id = p.id
                 WHERE p.id = ?
                   AND p.status = 'dipinjam'
                 GROUP BY p.id
                 FOR UPDATE"
            );

            $stmt->bind_param('i', $peminjamanId);
            $stmt->execute();

            $peminjaman = $stmt->get_result()->fetch_assoc();

            if (!$peminjaman) {
                throw new Exception(
                    'Transaksi tidak ditemukan atau sudah dikembalikan.'
                );
            }

            $tanggalPinjam = new DateTime($peminjaman['tanggal_pinjam']);
            $tanggalKembali = new DateTime(date('Y-m-d'));

            /*
             * Batas pengembalian = tanggal pinjam + lama pinjam.
             * Contoh: pinjam 1 Agustus selama 7 hari
             * berarti batas kembali 8 Agustus.
             */
            $tanggalJatuhTempo = clone $tanggalPinjam;
            $tanggalJatuhTempo->modify(
                '+' . (int) $peminjaman['lama_pinjam'] . ' days'
            );

            $terlambat = 0;

            if ($tanggalKembali > $tanggalJatuhTempo) {
                $selisih = $tanggalJatuhTempo->diff($tanggalKembali);
                $terlambat = (int) $selisih->days;
            }

            /*
             * Denda = jumlah buku x jumlah hari terlambat
             *         x tarif denda per buku per hari.
             */
            $jumlahBuku = (int) $peminjaman['jumlah_buku'];
            $denda = $jumlahBuku
                * $terlambat
                * DENDA_PER_BUKU_PER_HARI;

            $userId = (int) $_SESSION['user_id'];
            $tanggalKembaliSql = $tanggalKembali->format('Y-m-d');

            $stmt = $conn->prepare(
                'INSERT INTO pengembalian (
                    peminjaman_id,
                    tanggal_kembali,
                    keterlambatan_hari,
                    denda,
                    user_id
                ) VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'isiii',
                $peminjamanId,
                $tanggalKembaliSql,
                $terlambat,
                $denda,
                $userId
            );

            $stmt->execute();

            $stmt = $conn->prepare(
                "UPDATE peminjaman
                 SET status = 'sudah dikembalikan'
                 WHERE id = ?"
            );

            $stmt->bind_param('i', $peminjamanId);
            $stmt->execute();

            $conn->commit();

            if ($denda > 0) {
                $msg = 'Buku berhasil dikembalikan. Denda: Rp ' .
                    number_format($denda, 0, ',', '.');
            } else {
                $msg = 'Buku berhasil dikembalikan tanpa denda.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $err = $e->getMessage();
        }
    }
}

/*
 * Daftar transaksi yang masih dipinjam.
 * Jatuh tempo dan estimasi denda dihitung berdasarkan tanggal hari ini.
 */
$rows = $conn->query(
    "SELECT
        p.id,
        p.tanggal_pinjam,
        p.lama_pinjam,
        a.nama AS anggota,
        COALESCE(SUM(pd.jumlah), 0) AS jumlah_buku,
        GROUP_CONCAT(
            CONCAT(b.judul, ' (', pd.jumlah, ')')
            SEPARATOR ', '
        ) AS buku
     FROM peminjaman p
     JOIN anggota a
        ON a.id = p.anggota_id
     JOIN peminjaman_detail pd
        ON pd.peminjaman_id = p.id
     JOIN buku b
        ON b.id = pd.buku_id
     WHERE p.status = 'dipinjam'
     GROUP BY p.id
     ORDER BY p.tanggal_pinjam DESC, p.id DESC"
);

$riwayat = $conn->query(
    "SELECT
        p.id,
        a.nama AS anggota,
        p.tanggal_pinjam,
        p.lama_pinjam,
        pr.tanggal_kembali,
        pr.keterlambatan_hari,
        pr.denda
     FROM pengembalian pr
     JOIN peminjaman p
        ON p.id = pr.peminjaman_id
     JOIN anggota a
        ON a.id = p.anggota_id
     ORDER BY pr.tanggal_kembali DESC, pr.id DESC"
);

$pageTitle = 'Pengembalian';
$pageDescription = 'Kelola pengembalian dan denda keterlambatan.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($msg): ?>
    <div class="alert success">
        ✓ <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="alert danger">
        ! <?= e($err) ?>
    </div>
<?php endif; ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h3>Daftar Buku Sedang Dipinjam</h3>
            <p class="muted">
                Denda dihitung otomatis saat buku dikembalikan.
            </p>
        </div>

        <span class="badge red">Aktif</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Anggota</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Estimasi Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows->num_rows): ?>

                    <?php while ($row = $rows->fetch_assoc()): ?>
                        <?php
                        $tanggalPinjam = new DateTime($row['tanggal_pinjam']);
                        $jatuhTempo = clone $tanggalPinjam;
                        $jatuhTempo->modify(
                            '+' . (int) $row['lama_pinjam'] . ' days'
                        );

                        $hariTerlambat = 0;
                        $hariIni = new DateTime(date('Y-m-d'));

                        if ($hariIni > $jatuhTempo) {
                            $hariTerlambat = $jatuhTempo->diff($hariIni)->days;
                        }

                        $estimasiDenda =
                            (int) $row['jumlah_buku']
                            * $hariTerlambat
                            * DENDA_PER_BUKU_PER_HARI;
                        ?>

                        <tr>
                            <td data-label="ID">
                                #<?= (int) $row['id'] ?>
                            </td>

                            <td data-label="Anggota">
                                <?= e($row['anggota']) ?>
                            </td>

                            <td data-label="Buku">
                                <?= e($row['buku']) ?>
                            </td>

                            <td data-label="Tanggal Pinjam">
                                <?= e($row['tanggal_pinjam']) ?>
                            </td>

                            <td data-label="Jatuh Tempo">
                                <?= e($jatuhTempo->format('Y-m-d')) ?>
                            </td>

                            <td data-label="Estimasi Denda">
                                <?php if ($estimasiDenda > 0): ?>
                                    <span class="badge red">
                                        Rp <?= number_format($estimasiDenda, 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge green">
                                        Rp 0
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Aksi">
                                <form method="post">
                                    <input
                                        type="hidden"
                                        name="peminjaman_id"
                                        value="<?= (int) $row['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn small primary"
                                        onclick="return confirm('Kembalikan buku pada transaksi ini?')"
                                    >
                                        Kembalikan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">
                            ✓ Tidak ada buku yang sedang dipinjam.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h3>Riwayat Pengembalian</h3>
            <p class="muted">
                Denda yang tercatat pada setiap pengembalian.
            </p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Anggota</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Terlambat</th>
                    <th>Denda</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($riwayat->num_rows): ?>

                    <?php while ($row = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID">
                                #<?= (int) $row['id'] ?>
                            </td>

                            <td data-label="Anggota">
                                <?= e($row['anggota']) ?>
                            </td>

                            <td data-label="Tanggal Pinjam">
                                <?= e($row['tanggal_pinjam']) ?>
                            </td>

                            <td data-label="Tanggal Kembali">
                                <?= e($row['tanggal_kembali']) ?>
                            </td>

                            <td data-label="Terlambat">
                                <?= (int) $row['keterlambatan_hari'] ?> hari
                            </td>

                            <td data-label="Denda">
                                <strong>
                                    Rp <?= number_format((int) $row['denda'], 0, ',', '.') ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada riwayat pengembalian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
