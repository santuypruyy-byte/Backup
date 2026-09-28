PERPUSTAKAAN - VERSI DIPERBAIKI

1. Pastikan XAMPP Apache dan MySQL aktif.
2. Letakkan folder project sebagai:
   C:\xampp\htdocs\perpustakaan
3. Import:
   database/buku.sql
   ke database bernama: buku
4. Buka:
   http://localhost/perpustakaan/

AKUN DEMO
- Admin: admin / admin123
- User: user1 / 12345 (terhubung ke AG001)
- User: user2 / 12345 (terhubung ke AG002)
- User: user3 / 12345 (terhubung ke AG003)
- dst.

FITUR UTAMA
- Login dan logout
- Role admin dan user
- User terhubung ke satu anggota
- User hanya dapat meminjam atas nama anggota yang terhubung
- Admin dapat memilih anggota
- CRUD buku, kategori, penerbit, anggota, user
- Pencarian/OPAC
- Peminjaman
- Pengembalian
- Perhitungan denda
- Riwayat peminjaman

JIKA DATABASE LAMA SUDAH ADA
Jangan import buku.sql ke database lama tanpa backup.
Gunakan database/migrasi_user_anggota.sql untuk menambahkan relasi user -> anggota.
Pastikan database yang dipakai config/connection.php adalah database yang benar.
