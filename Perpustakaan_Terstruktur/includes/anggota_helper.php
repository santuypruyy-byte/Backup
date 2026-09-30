<?php

/*
 * Helper anggota yang dipakai bersama oleh form admin (user/form.php).
 * Isinya sama dengan buatKodeAnggota() di auth/daftar.php.
 * Dibungkus function_exists agar tidak bentrok jika suatu saat
 * daftar.php ikut memakai file ini.
 */

if (!function_exists('buatKodeAnggota')) {
    function buatKodeAnggota(mysqli $conn): string
    {
        // FOR UPDATE menahan proses lain sampai transaksi ini selesai,
        // supaya dua orang tidak mendapat kode yang sama.
        $res = $conn->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(kode_anggota, 3) AS UNSIGNED)), 0) AS terakhir
             FROM anggota
             WHERE kode_anggota REGEXP '^AG[0-9]+$'
             FOR UPDATE"
        );
        $terakhir = (int) $res->fetch_assoc()['terakhir'];

        return 'AG' . str_pad((string) ($terakhir + 1), 3, '0', STR_PAD_LEFT);
    }
}