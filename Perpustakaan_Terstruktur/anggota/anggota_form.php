<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();
require_once __DIR__ . "/../config/connection.php";
$id = (int) ($_GET["id"] ?? ($_POST["id"] ?? 0));
$d = [
  "kode_anggota" => "",
  "nama" => "",
  "jenis_kelamin" => "",
  "tempat_lahir" => "",
  "tanggal_lahir" => "",
  "telpon" => "",
  "alamat" => "",
];
if ($id) {
  $q = $conn->prepare("SELECT * FROM anggota WHERE id=?");
  $q->bind_param("i", $id);
  $q->execute();
  $d = $q->get_result()->fetch_assoc() ?: $d;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $v = [
    trim($_POST["kode_anggota"] ?? ""),
    trim($_POST["nama"] ?? ""),
    $_POST["jenis_kelamin"] ?? "",
    trim($_POST["tempat_lahir"] ?? ""),
    $_POST["tanggal_lahir"] ?? null,
    trim($_POST["telpon"] ?? ""),
    trim($_POST["alamat"] ?? ""),
  ];
  if ($id) {
    $q = $conn->prepare(
      "UPDATE anggota SET kode_anggota=?,nama=?,jenis_kelamin=?,tempat_lahir=?,tanggal_lahir=?,telpon=?,alamat=? WHERE id=?"
    );
    $q->bind_param(
      "sssssssi",
      $v[0],
      $v[1],
      $v[2],
      $v[3],
      $v[4],
      $v[5],
      $v[6],
      $id
    );
  } else {
    $q = $conn->prepare(
      "INSERT INTO anggota(kode_anggota,nama,jenis_kelamin,tempat_lahir,tanggal_lahir,telpon,alamat) VALUES(?,?,?,?,?,?,?)"
    );
    $q->bind_param("sssssss", $v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6]);
  }
  $q->execute();
  header("Location: " . url("anggota/anggota.php"));
  exit();
}
$pageTitle = $id ? "Edit Anggota" : "Tambah Anggota";
$pageDescription = $id ? "Perbarui data anggota." : "Tambahkan anggota baru.";
require_once __DIR__ . "/../includes/layout_header.php";
?>
<section class="panel form-panel">
    <div class="form-title"><span class="page-label">ANGGOTA</span>
        <h1><?= e($pageTitle) ?></h1>
        <p>Lengkapi informasi anggota dengan benar.</p>
    </div>
    <form method="post" class="form-grid"><input type="hidden" name="id" value="<?= $id ?>">
        <div><label>Kode Anggota</label><input name="kode_anggota" value="<?= e(
          $d["kode_anggota"]
        ) ?>" required></div>
        <div><label>Nama</label><input name="nama" value="<?= e(
          $d["nama"]
        ) ?>" required></div>
        <div><label>Jenis Kelamin</label><select name="jenis_kelamin">
                <option value="">Pilih</option>
                <option value="pria" <?= $d["jenis_kelamin"] === "pria"
                  ? "selected"
                  : "" ?>>Pria</option>
                <option value="wanita" <?= $d["jenis_kelamin"] === "wanita"
                  ? "selected"
                  : "" ?>>Wanita</option>
            </select></div>
        <div><label>Tempat Lahir</label><input name="tempat_lahir" value="<?= e(
          $d["tempat_lahir"]
        ) ?>"></div>
        <div><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="<?= e(
          $d["tanggal_lahir"]
        ) ?>"></div>
        <div><label>Telpon</label><input name="telpon" value="<?= e(
          $d["telpon"]
        ) ?>"></div>
        <div class="full"><label>Alamat</label><textarea name="alamat" rows="4"><?= e(
          $d["alamat"]
        ) ?></textarea></div>
        <div class="full form-actions"><button class="btn primary">Simpan</button><a href="<?= e(
          url("anggota/anggota.php")
        ) ?>" class="btn">Kembali</a></div>
    </form>
</section>
<?php require_once __DIR__ . "/../includes/layout_footer.php"; ?>
