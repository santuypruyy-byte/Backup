<?php

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/anggota_helper.php';

// Error MySQL dilempar sebagai exception agar transaksi bisa di-rollback.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

$data = [
    'nama' => '',
    'username' => '',
    'level' => 'user',
    'anggota_id' => null,
];

// Data anggota untuk mode "buat anggota baru".
$ang = [
    'jenis_kelamin' => '',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
    'telpon' => '',
    'alamat' => '',
];

$modeAnggota = 'baru';   // 'baru' atau 'lama'
$anggotaPilih = 0;       // dipakai jika mode 'lama'
$error = '';


/*
|--------------------------------------------------------------------------
| Ambil data user ketika edit
|--------------------------------------------------------------------------
*/

if ($id > 0) {
    $query = $conn->prepare("
        SELECT id, nama, username, level, anggota_id
        FROM user
        WHERE id = ?
        LIMIT 1
    ");
    $query->bind_param('i', $id);
    $query->execute();

    $found = $query->get_result()->fetch_assoc();

    if ($found) {
        $data = $found;
    } else {
        $id = 0;
    }
}

/*
| Anggota yang SUDAH terhubung dengan user ini (hanya saat edit).
| Jika ada, anggotanya tidak perlu dipilih/dibuat lagi.
*/
$anggotaTerhubung = (int) ($data['anggota_id'] ?? 0);
$infoAnggota = null;

if ($anggotaTerhubung > 0) {
    $q = $conn->prepare("SELECT id, kode_anggota, nama FROM anggota WHERE id = ? LIMIT 1");
    $q->bind_param('i', $anggotaTerhubung);
    $q->execute();
    $infoAnggota = $q->get_result()->fetch_assoc();

    if (!$infoAnggota) {
        $anggotaTerhubung = 0;
    }
}


/*
|--------------------------------------------------------------------------
| Proses form
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data['nama'] = trim($_POST['nama'] ?? '');
    $data['username'] = trim($_POST['username'] ?? '');
    $data['level'] = $_POST['level'] ?? 'user';

    $password = $_POST['password'] ?? '';

    $modeAnggota = ($_POST['mode_anggota'] ?? 'baru') === 'lama' ? 'lama' : 'baru';
    $anggotaPilih = (int) ($_POST['anggota_id'] ?? 0);

    foreach (array_keys($ang) as $k) {
        $ang[$k] = trim($_POST[$k] ?? '');
    }

    // Apakah akun ini perlu anggota yang belum ada (dibuat/dipilih sekarang)?
    $perluAnggota = $data['level'] === 'user' && $anggotaTerhubung < 1;


    /* ---------- Validasi akun ---------- */

    if ($data['nama'] === '') {
        $error = 'Nama wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 100) {
        $error = 'Nama maksimal 100 karakter.';
    } elseif ($data['username'] === '') {
        $error = 'Username wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $data['username'])) {
        $error = 'Username 3-100 karakter, hanya huruf, angka, titik, garis bawah, atau strip.';
    } elseif (!in_array($data['level'], ['admin', 'user'], true)) {
        $error = 'Level akun tidak valid.';
    } elseif ($id === 0 && $password === '') {
        $error = 'Password wajib diisi untuk user baru.';
    } elseif ($password !== '' && strlen($password) < 5) {
        $error = 'Password minimal 5 karakter.';
    } elseif (strlen($password) > 72) {
        // Batas aman bcrypt agar password tidak terpotong diam-diam.
        $error = 'Password maksimal 72 karakter.';
    }


    /* ---------- Validasi anggota (hanya jika perlu) ---------- */

    if ($error === '' && $perluAnggota) {

        if ($modeAnggota === 'baru') {

            $tgl = DateTime::createFromFormat('Y-m-d', $ang['tanggal_lahir']);
            $tglValid = $tgl && $tgl->format('Y-m-d') === $ang['tanggal_lahir'];

            if (
                $ang['jenis_kelamin'] === '' || $ang['tempat_lahir'] === '' ||
                $ang['tanggal_lahir'] === '' || $ang['telpon'] === '' ||
                $ang['alamat'] === ''
            ) {
                $error = 'Data anggota wajib diisi lengkap.';
            } elseif (!in_array($ang['jenis_kelamin'], ['pria', 'wanita'], true)) {
                $error = 'Jenis kelamin tidak valid.';
            } elseif (mb_strlen($ang['tempat_lahir']) > 100) {
                $error = 'Tempat lahir maksimal 100 karakter.';
            } elseif (!$tglValid || $ang['tanggal_lahir'] > date('Y-m-d')) {
                $error = 'Tanggal lahir tidak valid.';
            } elseif (!preg_match('/^[0-9]{8,12}$/', $ang['telpon'])) {
                $error = 'Nomor telpon harus 8-12 digit angka.';
            } else {
                // Cegah anggota ganda (nama + tanggal lahir sama).
                $cek = $conn->prepare("
                    SELECT id FROM anggota
                    WHERE nama = ? AND tanggal_lahir = ?
                    LIMIT 1
                ");
                $cek->bind_param('ss', $data['nama'], $ang['tanggal_lahir']);
                $cek->execute();

                if ($cek->get_result()->num_rows > 0) {
                    $error = 'Anggota dengan nama dan tanggal lahir tersebut sudah ada. '
                           . 'Gunakan pilihan "Hubungkan ke anggota yang sudah ada".';
                }
            }

        } else {

            if ($anggotaPilih < 1) {
                $error = 'Pilih anggota yang akan dihubungkan.';
            } else {
                $cek = $conn->prepare("SELECT id FROM anggota WHERE id = ? LIMIT 1");
                $cek->bind_param('i', $anggotaPilih);
                $cek->execute();

                if ($cek->get_result()->num_rows === 0) {
                    $error = 'Anggota tidak ditemukan.';
                } else {
                    $cek = $conn->prepare("SELECT id FROM user WHERE anggota_id = ? AND id <> ? LIMIT 1");
                    $cek->bind_param('ii', $anggotaPilih, $id);
                    $cek->execute();

                    if ($cek->get_result()->num_rows > 0) {
                        $error = 'Anggota tersebut sudah terhubung dengan akun user lain.';
                    }
                }
            }
        }
    }


    /* ---------- Cek username unik ---------- */

    if ($error === '') {
        $cek = $conn->prepare("SELECT id FROM user WHERE username = ? AND id <> ? LIMIT 1");
        $cek->bind_param('si', $data['username'], $id);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        }
    }


    /* ---------- Simpan (anggota + user dalam satu transaksi) ---------- */

    if ($error === '') {

        try {
            $conn->begin_transaction();

            // Tentukan anggota_id untuk akun ini.
            if ($data['level'] === 'admin') {
                $anggotaId = 0;                       // admin tidak punya anggota
            } elseif ($anggotaTerhubung > 0) {
                $anggotaId = $anggotaTerhubung;       // sudah terhubung, biarkan
            } elseif ($modeAnggota === 'lama') {
                $anggotaId = $anggotaPilih;           // hubungkan ke anggota yang ada
            } else {
                // Buat anggota baru otomatis.
                $kode = buatKodeAnggota($conn);

                $stmtA = $conn->prepare("
                    INSERT INTO anggota
                        (kode_anggota, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, telpon, alamat)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtA->bind_param(
                    'sssssss',
                    $kode,
                    $data['nama'],
                    $ang['jenis_kelamin'],
                    $ang['tempat_lahir'],
                    $ang['tanggal_lahir'],
                    $ang['telpon'],
                    $ang['alamat']
                );
                $stmtA->execute();
                $anggotaId = (int) $conn->insert_id;
            }

            if ($id > 0) {
                // EDIT
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmtU = $conn->prepare("
                        UPDATE user
                        SET nama = ?, username = ?, password = ?, level = ?, anggota_id = NULLIF(?, 0)
                        WHERE id = ?
                    ");
                    $stmtU->bind_param(
                        'ssssii',
                        $data['nama'], $data['username'], $hash,
                        $data['level'], $anggotaId, $id
                    );
                } else {
                    $stmtU = $conn->prepare("
                        UPDATE user
                        SET nama = ?, username = ?, level = ?, anggota_id = NULLIF(?, 0)
                        WHERE id = ?
                    ");
                    $stmtU->bind_param(
                        'sssii',
                        $data['nama'], $data['username'],
                        $data['level'], $anggotaId, $id
                    );
                }
                $stmtU->execute();

                if ($id === (int) $_SESSION['user_id']) {
                    $_SESSION['nama'] = $data['nama'];
                    $_SESSION['level'] = $data['level'];
                    $_SESSION['anggota_id'] = $anggotaId > 0 ? $anggotaId : null;
                }

            } else {
                // TAMBAH
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmtU = $conn->prepare("
                    INSERT INTO user (nama, username, password, level, anggota_id)
                    VALUES (?, ?, ?, ?, NULLIF(?, 0))
                ");
                $stmtU->bind_param(
                    'ssssi',
                    $data['nama'], $data['username'], $hash,
                    $data['level'], $anggotaId
                );
                $stmtU->execute();
            }

            $conn->commit();

            header('Location: ' . url('user/index.php'));
            exit;

        } catch (mysqli_sql_exception $ex) {
            $conn->rollback();

            // 1062 = duplicate entry (username / kode anggota bentrok).
            $error = $ex->getCode() === 1062
                ? 'Data bentrok (username atau kode anggota sudah ada), silakan coba lagi.'
                : 'Gagal menyimpan data: ' . $ex->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Daftar anggota yang BELUM punya akun (untuk mode "hubungkan")
|--------------------------------------------------------------------------
*/

$anggotaRows = $conn->query("
    SELECT a.id, a.kode_anggota, a.nama
    FROM anggota a
    LEFT JOIN user u ON u.anggota_id = a.id
    WHERE u.id IS NULL
    ORDER BY a.nama ASC
");


$pageTitle = $id > 0 ? 'Edit User' : 'Tambah User';

$pageDescription = $id > 0
    ? 'Perbarui data akun pengguna.'
    : 'Tambahkan akun administrator atau user.';

require_once __DIR__ . '/../includes/layout_header.php';

// Bagian anggota hanya ditampilkan jika akun belum punya anggota.
$tampilBagianAnggota = $anggotaTerhubung < 1;

?>

<?php if ($error): ?>
    <div class="alert danger"><?= e($error) ?></div>
<?php endif; ?>


<style>
    /*
     * style.css memberi input { width: 100% } dan label { display: block }
     * untuk semua elemen, sehingga radio ikut melebar dan terpisah dari
     * teksnya. Aturan di bawah hanya berlaku di bagian pilihan anggota ini.
     */
    #anggota-wrapper {
        display: grid;
        gap: 14px;
    }

    #anggota-wrapper > label {
        margin-bottom: 0;
    }

    .mode-anggota {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .mode-anggota .radio-opt {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 10px 14px;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        background: #fbfcfe;
        font-size: 13px;
        cursor: pointer;
    }

    .mode-anggota .radio-opt input[type="radio"] {
        width: auto;
        margin: 0;
        padding: 0;
        border: 0;
        background: none;
        box-shadow: none;
        accent-color: #2563eb;
    }

    .mode-anggota .radio-opt:has(input:checked) {
        border-color: #2563eb;
        background: #eff6ff;
    }
</style>

<section class="panel form-panel">

    <div class="form-title">
        <span class="page-label">DATA MASTER</span>
        <h1><?= e($pageTitle) ?></h1>
        <p>Kelola akun dan hubungan akun dengan anggota.</p>
    </div>

    <form method="post" class="form-grid">

        <input type="hidden" name="id" value="<?= $id ?>">

        <div>
            <label>Nama</label>
            <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>
        </div>

        <div>
            <label>Username</label>
            <input type="text" name="username" value="<?= e($data['username']) ?>" required>
        </div>

        <div>
            <label>Password <?= $id ? '(kosongkan jika tidak diubah)' : '' ?></label>
            <input
                type="password"
                name="password"
                <?= $id ? '' : 'required' ?>
                autocomplete="new-password"
            >
        </div>

        <div>
            <label>Level</label>
            <select name="level" id="level" required>
                <option value="user" <?= $data['level'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $data['level'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>


        <?php if (!$tampilBagianAnggota): ?>

            <!-- Akun sudah terhubung ke anggota: cukup ditampilkan. -->
            <div class="full" id="info-anggota">
                <label>Anggota</label>
                <p>
                    <b><?= e($infoAnggota['kode_anggota'] . ' - ' . $infoAnggota['nama']) ?></b>
                    &middot;
                    <a href="<?= e(url('anggota/anggota_form.php?id=' . (int) $infoAnggota['id'])) ?>">
                        Edit data anggota
                    </a>
                </p>
                <small class="muted">
                    Akun ini sudah terhubung dengan anggota. Jika level diubah menjadi Admin,
                    hubungan dengan anggota akan dilepas.
                </small>
            </div>

        <?php else: ?>

            <!-- Bagian anggota: hanya tampil untuk level User. -->
            <div class="full" id="anggota-wrapper">

                <label>Data Anggota</label>

                <div class="mode-anggota">
                    <label class="radio-opt">
                        <input type="radio" name="mode_anggota" value="baru"
                               <?= $modeAnggota === 'baru' ? 'checked' : '' ?>>
                        <span>Buat anggota baru (kode dibuat otomatis)</span>
                    </label>
                    <label class="radio-opt">
                        <input type="radio" name="mode_anggota" value="lama"
                               <?= $modeAnggota === 'lama' ? 'checked' : '' ?>>
                        <span>Hubungkan ke anggota yang sudah ada</span>
                    </label>
                </div>

                <!-- Mode: anggota baru -->
                <div class="form-grid" id="anggota-baru">

                    <div>
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" data-req="1">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="pria" <?= $ang['jenis_kelamin'] === 'pria' ? 'selected' : '' ?>>Pria</option>
                            <option value="wanita" <?= $ang['jenis_kelamin'] === 'wanita' ? 'selected' : '' ?>>Wanita</option>
                        </select>
                    </div>

                    <div>
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" maxlength="100" data-req="1"
                               value="<?= e($ang['tempat_lahir']) ?>">
                    </div>

                    <div>
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" max="<?= e(date('Y-m-d')) ?>" data-req="1"
                               value="<?= e($ang['tanggal_lahir']) ?>">
                    </div>

                    <div>
                        <label>Telpon</label>
                        <input type="text" name="telpon" inputmode="numeric" maxlength="12"
                               pattern="[0-9]{8,12}" title="8-12 digit angka" data-req="1"
                               value="<?= e($ang['telpon']) ?>">
                    </div>

                    <div class="full">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" data-req="1"><?= e($ang['alamat']) ?></textarea>
                    </div>

                </div>

                <!-- Mode: hubungkan anggota lama -->
                <div id="anggota-lama">
                    <label>Anggota</label>
                    <select name="anggota_id" data-req="1">
                        <option value="">Pilih anggota</option>
                        <?php while ($a = $anggotaRows->fetch_assoc()): ?>
                            <option value="<?= (int) $a['id'] ?>"
                                <?= $anggotaPilih === (int) $a['id'] ? 'selected' : '' ?>>
                                <?= e($a['kode_anggota'] . ' - ' . $a['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="muted">
                        Hanya anggota yang belum punya akun yang ditampilkan.
                    </small>
                </div>

            </div>

        <?php endif; ?>


        <div class="full form-actions">
            <button type="submit" class="btn primary">Simpan</button>
            <a href="<?= e(url('user/index.php')) ?>" class="btn">Kembali</a>
        </div>

    </form>

</section>


<?php if ($tampilBagianAnggota): ?>
<script>
const level = document.getElementById('level');
const wrapper = document.getElementById('anggota-wrapper');
const boxBaru = document.getElementById('anggota-baru');
const boxLama = document.getElementById('anggota-lama');
const radios = document.querySelectorAll('input[name="mode_anggota"]');

// Tampilkan/sembunyikan sebuah blok, dan nonaktifkan input di dalamnya
// supaya tidak ikut divalidasi browser maupun dikirim saat tersembunyi.
function toggle(box, show) {
    box.style.display = show ? '' : 'none';
    box.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.disabled = !show;
        el.required = show && el.dataset.req === '1';
    });
}

function update() {
    const isUser = level.value === 'user';
    const mode = document.querySelector('input[name="mode_anggota"]:checked').value;

    wrapper.style.display = isUser ? '' : 'none';

    // Radio mode ikut dinonaktifkan untuk admin.
    radios.forEach(function (r) { r.disabled = !isUser; });

    toggle(boxBaru, isUser && mode === 'baru');
    toggle(boxLama, isUser && mode === 'lama');
}

level.addEventListener('change', update);
radios.forEach(function (r) { r.addEventListener('change', update); });
update();
</script>
<?php endif; ?>


<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>