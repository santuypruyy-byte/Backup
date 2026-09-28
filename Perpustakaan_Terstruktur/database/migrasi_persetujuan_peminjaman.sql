-- Migrasi fitur persetujuan peminjaman
-- Jalankan pada database buku_ukk (atau database yang digunakan project).
-- Status:
--   diproses            = permintaan user menunggu ACC admin
--   dipinjam            = sudah disetujui admin dan buku boleh dipinjam
--   sudah dikembalikan  = buku telah dikembalikan
--   ditolak             = permintaan ditolak admin

ALTER TABLE `peminjaman`
  MODIFY `status` ENUM('diproses','dipinjam','sudah dikembalikan','ditolak')
  NOT NULL DEFAULT 'diproses';

-- Transaksi lama yang sebelumnya berstatus dipinjam tetap dianggap sudah disetujui.
-- Tidak perlu UPDATE karena nilai 'dipinjam' tetap dipertahankan.
