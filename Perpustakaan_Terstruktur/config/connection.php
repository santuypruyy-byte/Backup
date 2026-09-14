<?php
require_once __DIR__ . '/app.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "buku_ukk";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
