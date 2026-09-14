<?php
function uploadGambarBuku($fieldName = "gambar", $required = false)
{
  if (
    !isset($_FILES[$fieldName]) ||
    $_FILES[$fieldName]["error"] === UPLOAD_ERR_NO_FILE
  ) {
    if ($required) {
      return ["success" => false, "error" => "Silakan pilih gambar buku."];
    }
    return ["success" => true, "filename" => null];
  }

  $file = $_FILES[$fieldName];
  if ($file["error"] !== UPLOAD_ERR_OK) {
    return [
      "success" => false,
      "error" => "Upload gambar gagal. Kode error: " . $file["error"],
    ];
  }

  if ($file["size"] > 2 * 1024 * 1024) {
    return ["success" => false, "error" => "Ukuran gambar maksimal 2 MB."];
  }

  $allowed = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp",
  ];

  $mime = "";
  if (function_exists("finfo_open")) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);
  } else {
    $mime = mime_content_type($file["tmp_name"]);
  }

  if (!isset($allowed[$mime])) {
    return [
      "success" => false,
      "error" => "Format gambar harus JPG, JPEG, PNG, atau WEBP.",
    ];
  }

  if (@getimagesize($file["tmp_name"]) === false) {
    return [
      "success" => false,
      "error" => "File yang dipilih bukan gambar yang valid.",
    ];
  }

  $folder = __DIR__ . "/../assets/images/";
  if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
    return [
      "success" => false,
      "error" => "Folder penyimpanan gambar tidak dapat dibuat.",
    ];
  }

  $filename = "buku_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
  $destination = $folder . $filename;

  if (!move_uploaded_file($file["tmp_name"], $destination)) {
    return [
      "success" => false,
      "error" =>
        "Gambar gagal disimpan ke server. Pastikan folder assets/images dapat ditulis.",
    ];
  }

  return ["success" => true, "filename" => $filename, "path" => $destination];
}
