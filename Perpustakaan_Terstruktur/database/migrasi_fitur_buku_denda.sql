-- =========================================================
-- MIGRASI DATABASE PERPUSTAKAAN
-- 1. Menghapus field kode_buku dari tabel buku
-- 2. Menambahkan field denda pada tabel pengembalian
-- =========================================================

-- Jalankan file ini SATU KALI pada database buku yang sudah ada.

ALTER TABLE buku
    DROP COLUMN kode_buku;

ALTER TABLE pengembalian
    ADD COLUMN keterlambatan_hari INT UNSIGNED NOT NULL DEFAULT 0
        AFTER tanggal_kembali,
    ADD COLUMN denda INT UNSIGNED NOT NULL DEFAULT 0
        AFTER keterlambatan_hari;

-- Tarif denda aplikasi:
-- Rp1.000 per buku per hari keterlambatan.
-- Tarif dapat diubah di:
-- config/app.php -> DENDA_PER_BUKU_PER_HARI


-- 3. Mengganti role petugas menjadi user
-- Jalankan bagian ini jika database lama masih menggunakan role petugas.
ALTER TABLE user
    MODIFY COLUMN level ENUM('admin', 'petugas', 'user') NOT NULL;

UPDATE user
SET level = 'user'
WHERE level = 'petugas';

ALTER TABLE user
    MODIFY COLUMN level ENUM('admin', 'user') NOT NULL;

-- Hapus field ISBN dari tabel buku
ALTER TABLE buku DROP COLUMN IF EXISTS isbn;
