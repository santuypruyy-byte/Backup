-- Jalankan file ini SATU KALI pada database buku yang sudah terlanjur dibuat.
-- Fitur hapus buku menggunakan soft delete agar riwayat peminjaman tetap tersimpan.

ALTER TABLE buku
ADD COLUMN status_aktif TINYINT(1) NOT NULL DEFAULT 1 AFTER gambar;

-- Semua buku lama otomatis tetap aktif karena nilai default = 1.
