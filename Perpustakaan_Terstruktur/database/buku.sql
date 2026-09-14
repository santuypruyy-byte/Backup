-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Agu 2026 pada 04.28
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_anggota` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('pria','wanita') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `telpon` varchar(12) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `kode_anggota`, `nama`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `telpon`, `alamat`, `foto`) VALUES
(1, 'AG001', 'Ahmad', 'pria', 'Bandung', '2005-03-12', '081234567801', 'Bandung', 'ahmad.jpg'),
(2, 'AG002', 'Budi', 'pria', 'Cimahi', '2004-01-11', '081234567802', 'Cimahi', 'budi.jpg'),
(3, 'AG003', 'Citra', 'wanita', 'Bandung', '2006-07-10', '081234567803', 'Bandung', 'citra.jpg'),
(4, 'AG004', 'Dina', 'wanita', 'Garut', '2005-05-21', '081234567804', 'Garut', 'dina.jpg'),
(5, 'AG005', 'Eko', 'pria', 'Tasikmalaya', '2004-09-15', '081234567805', 'Tasikmalaya', 'eko.jpg'),
(6, 'AG006', 'Farah', 'wanita', 'Subang', '2006-02-17', '081234567806', 'Subang', 'farah.jpg'),
(7, 'AG007', 'Gilang', 'pria', 'Sumedang', '2005-12-05', '081234567807', 'Sumedang', 'gilang.jpg'),
(8, 'AG008', 'Hani', 'wanita', 'Bandung', '2006-08-14', '081234567808', 'Bandung', 'hani.jpg'),
(9, 'AG009', 'Indra', 'pria', 'Majalengka', '2004-06-18', '081234567809', 'Majalengka', 'indra.jpg'),
(10, 'AG010', 'Jihan', 'wanita', 'Cianjur', '2005-10-20', '081234567810', 'Cianjur', 'jihan.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori_id` int(10) UNSIGNED NOT NULL,
  `penerbit_id` int(10) UNSIGNED NOT NULL,
  `pengarang` varchar(150) DEFAULT NULL,
  `jumlah_halaman` int(3) DEFAULT NULL,
  `jumlah_stok` int(3) DEFAULT NULL,
  `tahun_terbit` int(4) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `kategori_id`, `penerbit_id`, `pengarang`, `jumlah_halaman`, `jumlah_stok`, `tahun_terbit`, `sinopsis`, `gambar`, `status_aktif`) VALUES
(1, 'Laskar Pelangi', 1, 2, 'Andrea Hirata', 350, 12, 2018, 'Novel inspiratif', 'bk1.jpg', 1),
(2, 'Naruto Vol.1', 2, 2, 'Masashi Kishimoto', 180, 20, 2016, 'Komik Jepang', 'bk2.jpg', 1),
(3, 'Pemrograman PHP', 4, 3, 'Jubilee Enterprise', 420, 8, 2022, 'Belajar PHP', 'bk3.jpg', 1),
(4, 'Basis Data', 4, 4, 'Rosa A.S.', 300, 10, 2021, 'Belajar Database', 'bk4.jpg', 1),
(5, 'Matematika XI', 3, 1, 'Kemendikbud', 280, 15, 2023, 'Buku Pelajaran', 'bk5.jpg', 1),
(6, 'Fiqih Islam', 5, 5, 'Abdul Karim', 250, 9, 2019, 'Agama Islam', 'bk6.jpg', 1),
(7, 'Sejarah Indonesia', 6, 6, 'Sartono', 330, 7, 2020, 'Sejarah Indonesia', 'bk7.jpg', 1),
(8, 'Biografi B.J. Habibie', 7, 8, 'Alberthiene', 290, 11, 2017, 'Biografi', 'bk8.jpg', 1),
(9, 'Fisika Dasar', 8, 7, 'Halliday', 510, 6, 2021, 'Fisika Dasar', 'bk9.jpg', 1),
(10, 'Kamus Bahasa Indonesia', 9, 10, 'Tim Bahasa', 700, 5, 2020, 'Kamus Bahasa', 'bk10.jpg', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_kategori` varchar(20) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `kode_kategori`, `nama_kategori`) VALUES
(1, 'KT001', 'Novel'),
(2, 'KT002', 'Komik'),
(3, 'KT003', 'Pelajaran'),
(4, 'KT004', 'Teknologi'),
(5, 'KT005', 'Agama'),
(6, 'KT006', 'Sejarah'),
(7, 'KT007', 'Biografi'),
(8, 'KT008', 'Sains'),
(9, 'KT009', 'Bahasa'),
(10, 'KT010', 'Ensiklopedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(10) UNSIGNED NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `lama_pinjam` int(2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('dipinjam','sudah dikembalikan') DEFAULT 'dipinjam',
  `anggota_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `tanggal_pinjam`, `lama_pinjam`, `keterangan`, `status`, `anggota_id`, `user_id`) VALUES
(1, '2026-08-01', 7, 'Baik', 'dipinjam', 1, 2),
(2, '2026-08-02', 5, 'Baik', 'dipinjam', 2, 3),
(3, '2026-08-03', 7, 'Baik', 'dipinjam', 3, 4),
(4, '2026-08-04', 3, 'Baik', 'sudah dikembalikan', 4, 5),
(5, '2026-08-05', 7, 'Baik', 'dipinjam', 5, 6),
(6, '2026-08-06', 7, 'Baik', 'dipinjam', 6, 7),
(7, '2026-08-07', 5, 'Baik', 'sudah dikembalikan', 7, 8),
(8, '2026-08-08', 4, 'Baik', 'dipinjam', 8, 9),
(9, '2026-08-09', 7, 'Baik', 'dipinjam', 9, 10),
(10, '2026-08-10', 7, 'Baik', 'dipinjam', 10, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman_detail`
--

CREATE TABLE `peminjaman_detail` (
  `id` int(10) UNSIGNED NOT NULL,
  `peminjaman_id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `jumlah` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `peminjaman_detail`
--

INSERT INTO `peminjaman_detail` (`id`, `peminjaman_id`, `buku_id`, `jumlah`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 2),
(3, 3, 3, 1),
(4, 4, 4, 1),
(5, 5, 5, 2),
(6, 6, 6, 1),
(7, 7, 7, 1),
(8, 8, 8, 2),
(9, 9, 9, 1),
(10, 10, 10, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penerbit`
--

CREATE TABLE `penerbit` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_penerbit` varchar(20) NOT NULL,
  `nama_penerbit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `penerbit`
--

INSERT INTO `penerbit` (`id`, `kode_penerbit`, `nama_penerbit`) VALUES
(1, 'PB001', 'Erlangga'),
(2, 'PB002', 'Gramedia'),
(3, 'PB003', 'Andi Publisher'),
(4, 'PB004', 'Informatika'),
(5, 'PB005', 'Mizan'),
(6, 'PB006', 'Yudhistira'),
(7, 'PB007', 'Deepublish'),
(8, 'PB008', 'Bentang Pustaka'),
(9, 'PB009', 'Tiga Serangkai'),
(10, 'PB010', 'Pustaka Pelajar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id` int(10) UNSIGNED NOT NULL,
  `peminjaman_id` int(10) UNSIGNED NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `keterlambatan_hari` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `denda` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pengembalian`
--

INSERT INTO `pengembalian` (`id`, `peminjaman_id`, `tanggal_kembali`, `keterlambatan_hari`, `denda`, `user_id`) VALUES
(1, 1, '2026-08-08', 0, 0, 2),
(2, 2, '2026-08-07', 0, 0, 3),
(3, 3, '2026-08-10', 0, 0, 4),
(4, 4, '2026-08-07', 0, 0, 5),
(5, 5, '2026-08-12', 0, 0, 6),
(6, 6, '2026-08-13', 0, 0, 7),
(7, 7, '2026-08-12', 0, 0, 8),
(8, 8, '2026-08-12', 0, 0, 9),
(9, 9, '2026-08-16', 0, 0, 10),
(10, 10, '2026-08-17', 0, 0, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `level` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `username`, `password`, `level`) VALUES
(1, 'Administrator', 'admin', 'admin123', 'admin'),
(2, 'User 1', 'user1', '12345', 'user'),
(3, 'User 2', 'user2', '12345', 'user'),
(4, 'User 3', 'user3', '12345', 'user'),
(5, 'User 4', 'user4', '12345', 'user'),
(6, 'User 5', 'user5', '12345', 'user'),
(7, 'User 6', 'user6', '12345', 'user'),
(8, 'User 7', 'user7', '12345', 'user'),
(9, 'User 8', 'user8', '12345', 'user'),
(10, 'User 9', 'user9', '12345', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `penerbit_id` (`penerbit_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peminjaman_id` (`peminjaman_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peminjaman_id` (`peminjaman_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `buku_ibfk_2` FOREIGN KEY (`penerbit_id`) REFERENCES `penerbit` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD CONSTRAINT `peminjaman_detail_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `peminjaman_detail_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pengembalian_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
