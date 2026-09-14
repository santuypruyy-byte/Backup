PERPUSTAKAAN - STRUKTUR FOLDER RAPI

Struktur utama:
config/       koneksi dan konfigurasi aplikasi
includes/     auth, sidebar, layout header/footer
assets/       CSS, JavaScript, dan gambar
auth/         login, logout, daftar
buku/         CRUD dan detail buku
anggota/      data anggota
transaksi/    peminjaman dan pengembalian
pencarian/    pencarian buku dan endpoint kategori
dashboard/    dashboard utama
database/     file SQL

AWebServer:
1. Extract folder Perpustakaan ke folder yang digunakan sebagai web root AWebServer.
2. Import database/buku.sql ke MySQL/phpMyAdmin jika database belum tersedia.
3. Pastikan database bernama buku.
4. Buka folder project melalui browser. index.php otomatis mengarahkan ke auth/login.php.
5. Tidak perlu mengubah path CSS, JS, gambar, atau link halaman. BASE_URL dibuat otomatis oleh config/app.php.

Catatan gambar:
- Letakkan file gambar buku di assets/images/.
- Nilai kolom gambar pada database harus berisi nama file, misalnya bk1.jpg.
- Jika file gambar tidak ditemukan, sistem memakai assets/images/default.svg.

Login:
- Tampilan login tetap menggunakan desain login sebelumnya.
- CSS login tidak dipindahkan ke desain baru; hanya path stylesheet yang disesuaikan.


CATATAN LOGIN:
CSS login dan daftar dipisahkan ke assets/css/login.css dan menggunakan CSS lama dari project sebelumnya. Jangan hapus atau ganti file tersebut jika ingin tampilan login tetap seperti sebelumnya.

FITUR UPLOAD GAMBAR BUKU
- Tambah buku: pilih gambar langsung dari galeri/file manager HP.
- Format: JPG/JPEG, PNG, WEBP.
- Maksimal ukuran: 2 MB.
- Nama file dibuat otomatis dan disimpan di assets/images/.
- Database hanya menyimpan nama file gambar.
- Edit buku: pilih gambar baru untuk mengganti gambar lama; kosongkan jika tidak ingin mengganti.
- Hapus buku: gambar yang diupload aplikasi ikut dihapus jika buku berhasil dihapus.


FITUR HAPUS BUKU TANPA MENGHAPUS RIWAYAT
------------------------------------------
Buku yang dihapus menggunakan soft delete: status_aktif diubah menjadi 0.
Data buku tetap ada di database sehingga peminjaman_detail dan riwayat tetap aman.
Halaman katalog, pencarian, dashboard, dan form peminjaman hanya menampilkan buku aktif.
Halaman riwayat peminjaman/pengembalian tetap dapat menampilkan buku yang sudah diarsipkan.

Jika database buku sudah dibuat sebelum fitur ini ditambahkan, jalankan:
database/migrasi_hapus_buku.sql

Jika database diimpor dari database/buku.sql yang baru, kolom status_aktif sudah tersedia.


UPDATE FITUR:
- Field `kode_buku` pada tabel `buku` sudah dihapus.
- Denda keterlambatan otomatis dihitung saat pengembalian.
- Tarif denda default: Rp1.000/buku/hari.
- Jalankan `database/migrasi_fitur_buku_denda.sql` pada database lama.
- Jika membuat database dari awal, gunakan `database/buku.sql`.
