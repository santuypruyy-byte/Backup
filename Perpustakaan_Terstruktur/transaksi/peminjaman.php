<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';
$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota = (int)($_POST['anggota_id'] ?? 0);
    $buku = (int)($_POST['buku_id'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 1);
    $lama = (int)($_POST['lama_pinjam'] ?? 7);
    $ket = trim($_POST['keterangan'] ?? '');
    if ($anggota < 1 || $buku < 1 || $jumlah < 1 || $lama < 1) $err = 'Data peminjaman belum lengkap.';
    else {
        $conn->begin_transaction();
        try {
            $q = $conn->prepare('SELECT jumlah_stok FROM buku WHERE id=? AND status_aktif=1 FOR UPDATE');
            $q->bind_param('i', $buku);
            $q->execute();
            $bk = $q->get_result()->fetch_assoc();
            if (!$bk) throw new Exception('Buku tidak ditemukan.');
            $q = $conn->prepare("SELECT COALESCE(SUM(pd.jumlah),0) j FROM peminjaman_detail pd JOIN peminjaman p ON p.id=pd.peminjaman_id WHERE pd.buku_id=? AND p.status='dipinjam'");
            $q->bind_param('i', $buku);
            $q->execute();
            $dip = (int)$q->get_result()->fetch_assoc()['j'];
            if ((int)$bk['jumlah_stok'] - $dip < $jumlah) throw new Exception('Stok tersedia tidak mencukupi.');
            $tanggal = date('Y-m-d');
            $uid = (int)$_SESSION['user_id'];
            $status = 'dipinjam';
            $q = $conn->prepare('INSERT INTO peminjaman(tanggal_pinjam,lama_pinjam,keterangan,status,anggota_id,user_id) VALUES(?,?,?,?,?,?)');
            $q->bind_param('sissii', $tanggal, $lama, $ket, $status, $anggota, $uid);
            $q->execute();
            $pid = $conn->insert_id;
            $q = $conn->prepare('INSERT INTO peminjaman_detail(peminjaman_id,buku_id,jumlah) VALUES(?,?,?)');
            $q->bind_param('iii', $pid, $buku, $jumlah);
            $q->execute();
            $conn->commit();
            $msg = 'Peminjaman berhasil disimpan.';
        } catch (Exception $e) {
            $conn->rollback();
            $err = $e->getMessage();
        }
    }
}
$anggotaRs = $conn->query('SELECT id,kode_anggota,nama FROM anggota ORDER BY nama');
$bukuRs = $conn->query("SELECT b.id,b.judul,b.jumlah_stok-COALESCE(x.dipinjam,0) tersedia FROM buku b LEFT JOIN (SELECT pd.buku_id,SUM(pd.jumlah) dipinjam FROM peminjaman_detail pd JOIN peminjaman p ON p.id=pd.peminjaman_id WHERE p.status='dipinjam' GROUP BY pd.buku_id)x ON x.buku_id=b.id WHERE b.status_aktif=1 ORDER BY b.judul");
$rows = $conn->query("SELECT p.id,p.tanggal_pinjam,p.lama_pinjam,p.status,a.nama anggota,GROUP_CONCAT(CONCAT(b.judul,' (',pd.jumlah,')') SEPARATOR ', ') buku FROM peminjaman p JOIN anggota a ON a.id=p.anggota_id JOIN peminjaman_detail pd ON pd.peminjaman_id=p.id JOIN buku b ON b.id=pd.buku_id GROUP BY p.id ORDER BY p.id DESC");
$pageTitle = 'Peminjaman';
$pageDescription = 'Catat dan pantau transaksi peminjaman buku.';
require_once __DIR__ . '/../includes/layout_header.php';
?>
<?php if ($msg): ?><div class="alert success">✓ <?= e($msg) ?></div><?php endif; ?><?php if ($err): ?><div class="alert danger">! <?= e($err) ?></div><?php endif; ?>
<section class="panel form-panel">
    <div class="panel-head">
        <div>
            <h3>Transaksi Peminjaman</h3>
            <p class="muted">Pilih anggota dan buku yang akan dipinjam.</p>
        </div>
    </div>
    <form method="post" class="form-grid">
        <div><label>Anggota</label><select name="anggota_id" required>
                <option value="">Pilih anggota</option><?php while ($a = $anggotaRs->fetch_assoc()): ?><option value="<?= $a['id'] ?>"><?= e($a['kode_anggota'] . ' - ' . $a['nama']) ?></option><?php endwhile; ?>
            </select></div>
        <div><label>Buku</label><select name="buku_id" required>
                <option value="">Pilih buku</option><?php while ($b = $bukuRs->fetch_assoc()): ?><option value="<?= $b['id'] ?>" <?= $b['tersedia'] < 1 ? 'disabled' : '' ?>><?= e($b['judul']) ?> — tersedia <?= $b['tersedia'] ?></option><?php endwhile; ?>
            </select></div>
        <div><label>Jumlah</label><input type="number" name="jumlah" min="1" value="1" required></div>
        <div><label>Lama Pinjam (hari)</label><input type="number" name="lama_pinjam" min="1" value="7" required></div>
        <div class="full"><label>Keterangan</label><textarea name="keterangan" rows="4" placeholder="Keterangan tambahan (opsional)"></textarea></div>
        <div class="full form-actions"><button class="btn primary">Simpan Peminjaman</button></div>
    </form>
</section>
<section class="panel">
    <div class="panel-head">
        <div>
            <h3>Riwayat Peminjaman</h3>
            <p class="muted">Semua transaksi peminjaman.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Anggota</th>
                    <th>Buku</th>
                    <th>Tanggal</th>
                    <th>Lama</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody><?php while ($r = $rows->fetch_assoc()): ?><tr>
                        <td data-label="ID">#<?= $r['id'] ?></td>
                        <td data-label="Anggota"><?= e($r['anggota']) ?></td>
                        <td data-label="Buku"><?= e($r['buku']) ?></td>
                        <td data-label="Tanggal"><?= e($r['tanggal_pinjam']) ?></td>
                        <td data-label="Lama"><?= $r['lama_pinjam'] ?> hari</td>
                        <td data-label="Status"><span class="badge <?= $r['status'] === 'dipinjam' ? 'red' : 'green' ?>"><?= e($r['status']) ?></span></td>
                    </tr><?php endwhile; ?></tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>