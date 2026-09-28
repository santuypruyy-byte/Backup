-- =========================================================
-- DATABASE PERPUSTAKAAN - VERSI TERBARU
-- Database: buku_ukk
--
-- Fitur:
-- 1. Login admin/user
-- 2. User terhubung otomatis ke anggota melalui anggota_id
-- 3. Pengajuan peminjaman user -> status diproses
-- 4. Admin ACC -> status dipinjam
-- 5. Admin menolak -> status ditolak
-- 6. Pengembalian dan denda
-- 7. Koleksi buku dashboard -> langsung ke form peminjaman
-- 8. Soft delete buku melalui status_aktif
-- 9. OPAC/pencarian buku
-- =========================================================

CREATE DATABASE IF NOT EXISTS `buku_ukk`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `buku_ukk`;

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `rating_buku`;
DROP TABLE IF EXISTS `notifikasi`;
DROP TABLE IF EXISTS `bookmark`;
DROP TABLE IF EXISTS `pengembalian`;
DROP TABLE IF EXISTS `peminjaman_detail`;
DROP TABLE IF EXISTS `peminjaman`;
DROP TABLE IF EXISTS `buku`;
DROP TABLE IF EXISTS `penerbit`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `anggota`;

-- =========================================================
-- TABEL ANGGOTA
-- =========================================================

CREATE TABLE `anggota` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_anggota` VARCHAR(20) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `jenis_kelamin` ENUM('pria','wanita') DEFAULT NULL,
  `tempat_lahir` VARCHAR(100) DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `telpon` VARCHAR(20) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_anggota_kode` (`kode_anggota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `anggota`
(`id`,`kode_anggota`,`nama`,`jenis_kelamin`,`tempat_lahir`,`tanggal_lahir`,`telpon`,`alamat`,`foto`)
VALUES
(1,'AG001','Ahmad','pria','Bandung','2005-03-12','081234567801','Bandung','ahmad.jpg'),
(2,'AG002','Budi','pria','Cimahi','2004-01-11','081234567802','Cimahi','budi.jpg'),
(3,'AG003','Citra','wanita','Bandung','2006-07-10','081234567803','Bandung','citra.jpg'),
(4,'AG004','Dina','wanita','Garut','2005-05-21','081234567804','Garut','dina.jpg'),
(5,'AG005','Eko','pria','Tasikmalaya','2004-09-15','081234567805','Tasikmalaya','eko.jpg'),
(6,'AG006','Farah','wanita','Subang','2006-02-17','081234567806','Subang','farah.jpg'),
(7,'AG007','Gilang','pria','Sumedang','2005-12-05','081234567807','Sumedang','gilang.jpg'),
(8,'AG008','Hani','wanita','Bandung','2006-08-14','081234567808','Bandung','hani.jpg'),
(9,'AG009','Indra','pria','Majalengka','2004-06-18','081234567809','Majalengka','indra.jpg'),
(10,'AG010','Jihan','wanita','Sukabumi','2005-10-20','081234567810','Sukabumi','jihan.jpg');

-- =========================================================
-- TABEL KATEGORI
-- =========================================================

CREATE TABLE `kategori` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_kategori` VARCHAR(20) NOT NULL,
  `nama_kategori` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_kode` (`kode_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori` (`id`,`kode_kategori`,`nama_kategori`) VALUES
(1,'KT001','Novel'),
(2,'KT002','Komik'),
(3,'KT003','Pelajaran'),
(4,'KT004','Teknologi'),
(5,'KT005','Agama'),
(6,'KT006','Sejarah'),
(7,'KT007','Biografi'),
(8,'KT008','Sains'),
(9,'KT009','Bahasa'),
(10,'KT010','Ensiklopedia');

-- =========================================================
-- TABEL PENERBIT
-- =========================================================

CREATE TABLE `penerbit` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_penerbit` VARCHAR(20) NOT NULL,
  `nama_penerbit` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_penerbit_kode` (`kode_penerbit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `penerbit` (`id`,`kode_penerbit`,`nama_penerbit`) VALUES
(1,'PB001','Erlangga'),
(2,'PB002','Gramedia'),
(3,'PB003','Andi Publisher'),
(4,'PB004','Informatika'),
(5,'PB005','Mizan'),
(6,'PB006','Yudhistira'),
(7,'PB007','Deepublish'),
(8,'PB008','Bentang Pustaka'),
(9,'PB009','Tiga Serangkai'),
(10,'PB010','Pustaka Pelajar');

-- =========================================================
-- TABEL BUKU
-- =========================================================

CREATE TABLE `buku` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `kategori_id` INT UNSIGNED NOT NULL,
  `penerbit_id` INT UNSIGNED NOT NULL,
  `pengarang` VARCHAR(150) DEFAULT NULL,
  `jumlah_halaman` INT UNSIGNED DEFAULT NULL,
  `jumlah_stok` INT UNSIGNED DEFAULT 0,
  `tahun_terbit` SMALLINT UNSIGNED DEFAULT NULL,
  `sinopsis` TEXT DEFAULT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `rak` VARCHAR(50) DEFAULT NULL,
  `ebook_file` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_buku_kategori` (`kategori_id`),
  KEY `idx_buku_penerbit` (`penerbit_id`),
  KEY `idx_buku_status` (`status_aktif`),
  CONSTRAINT `fk_buku_kategori`
    FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_buku_penerbit`
    FOREIGN KEY (`penerbit_id`) REFERENCES `penerbit` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `buku`
(`id`,`judul`,`kategori_id`,`penerbit_id`,`pengarang`,`jumlah_halaman`,`jumlah_stok`,`tahun_terbit`,`sinopsis`,`gambar`,`status_aktif`,`views_count`,`rak`,`ebook_file`)
VALUES
(1,'Laskar Pelangi',1,2,'Andrea Hirata',350,12,2018,'Novel inspiratif','bk1.jpg',1,1,'A1',NULL),
(2,'Naruto Vol.1',2,2,'Masashi Kishimoto',180,20,2016,'Komik Jepang','bk2.jpg',1,1,'B1',NULL),
(3,'Pemrograman PHP',4,3,'Jubilee Enterprise',420,8,2022,'Belajar PHP','bk3.jpg',1,1,'C1',NULL),
(4,'Basis Data',4,4,'Rosa A.S.',300,10,2021,'Belajar Database','bk4.jpg',1,2,'C2',NULL),
(5,'Matematika XI',3,1,'Kemendikbud',280,15,2023,'Buku Pelajaran','bk5.jpg',0,1,'D1',NULL),
(6,'Fiqih Islam',5,5,'Abdul Karim',250,9,2019,'Agama Islam','bk6.jpg',1,1,'E1',NULL),
(7,'Sejarah Indonesia',6,6,'Sartono',330,7,2020,'Sejarah Indonesia','bk7.jpg',1,1,'F1',NULL),
(8,'Biografi B.J. Habibie',7,8,'Alberthiene',290,11,2017,'Biografi','bk8.jpg',1,1,'G1',NULL),
(9,'Fisika Dasar',8,7,'Halliday',510,6,2021,'Fisika Dasar','bk9.jpg',1,1,'H1',NULL),
(10,'Kamus Bahasa Indonesia',9,10,'Tim Bahasa',700,5,2020,'Kamus Bahasa','bk10.jpg',1,1,'I1',NULL),
(11,'Bumi',1,2,'Tere Liye',440,5,2014,'Novel petualangan','buku_d7f8f1f52afb156b.jpg',1,1,'A2',NULL);

-- =========================================================
-- TABEL USER
-- =========================================================

CREATE TABLE `user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `level` ENUM('admin','user') NOT NULL,
  `anggota_id` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_username` (`username`),
  UNIQUE KEY `uq_user_anggota` (`anggota_id`),
  KEY `idx_user_anggota` (`anggota_id`),
  CONSTRAINT `fk_user_anggota`
    FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user`
(`id`,`nama`,`username`,`password`,`level`,`anggota_id`)
VALUES
(1,'Administrator','admin','admin123','admin',NULL),
(2,'User 1','user1','12345','user',1),
(3,'User 2','user2','12345','user',2),
(4,'User 3','user3','12345','user',3),
(5,'User 4','user4','12345','user',4),
(6,'User 5','user5','12345','user',5),
(7,'User 6','user6','12345','user',6),
(8,'User 7','user7','12345','user',7),
(9,'User 8','user8','12345','user',8),
(10,'User 9','user9','12345','user',9),
(11,'User 10','user10','12345','user',10);

-- =========================================================
-- TABEL PEMINJAMAN
--
-- diproses            = menunggu ACC admin
-- dipinjam            = sudah di-ACC admin
-- sudah dikembalikan  = selesai
-- ditolak             = ditolak admin
-- =========================================================

CREATE TABLE `peminjaman` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tanggal_pinjam` DATE NOT NULL,
  `lama_pinjam` INT UNSIGNED DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `status` ENUM(
    'diproses',
    'dipinjam',
    'sudah dikembalikan',
    'ditolak'
  ) NOT NULL DEFAULT 'diproses',
  `anggota_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_peminjaman_anggota` (`anggota_id`),
  KEY `idx_peminjaman_user` (`user_id`),
  KEY `idx_peminjaman_status` (`status`),
  CONSTRAINT `fk_peminjaman_anggota`
    FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_peminjaman_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contoh transaksi:
-- user1 pernah meminjam buku dan sudah selesai.
-- user2 memiliki contoh pengajuan yang sedang diproses.
INSERT INTO `peminjaman`
(`id`,`tanggal_pinjam`,`lama_pinjam`,`keterangan`,`status`,`anggota_id`,`user_id`)
VALUES
(1,'2026-08-01',7,'Baik','sudah dikembalikan',1,2),
(2,'2026-08-02',5,'Baik','sudah dikembalikan',2,3),
(3,'2026-08-03',7,'Baik','sudah dikembalikan',3,4),
(4,'2026-08-04',3,'Baik','sudah dikembalikan',4,5),
(5,'2026-08-05',7,'Baik','sudah dikembalikan',5,6),
(6,'2026-08-06',7,'Baik','sudah dikembalikan',6,7),
(7,'2026-08-07',5,'Baik','sudah dikembalikan',7,8),
(8,'2026-08-08',4,'Baik','sudah dikembalikan',8,9),
(9,'2026-08-09',7,'Baik','sudah dikembalikan',9,10),
(10,'2026-08-10',7,'Baik','sudah dikembalikan',10,11),
(11,CURDATE(),7,'Contoh pengajuan menunggu ACC','diproses',1,2);

-- =========================================================
-- TABEL DETAIL PEMINJAMAN
-- =========================================================

CREATE TABLE `peminjaman_detail` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `peminjaman_id` INT UNSIGNED NOT NULL,
  `buku_id` INT UNSIGNED NOT NULL,
  `jumlah` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_detail_peminjaman` (`peminjaman_id`),
  KEY `idx_detail_buku` (`buku_id`),
  CONSTRAINT `fk_detail_peminjaman`
    FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_buku`
    FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `peminjaman_detail`
(`id`,`peminjaman_id`,`buku_id`,`jumlah`)
VALUES
(1,1,1,1),
(2,2,2,2),
(3,3,3,1),
(4,4,4,1),
(5,5,5,2),
(6,6,6,1),
(7,7,7,1),
(8,8,8,2),
(9,9,9,1),
(10,10,10,1),
(11,11,6,1);

-- =========================================================
-- TABEL PENGEMBALIAN
--
-- Pengembalian dicatat ketika admin menyelesaikan proses
-- pengembalian dari transaksi berstatus dipinjam.
-- =========================================================

CREATE TABLE `pengembalian` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `peminjaman_id` INT UNSIGNED NOT NULL,
  `tanggal_kembali` DATE NOT NULL,
  `keterlambatan_hari` INT UNSIGNED NOT NULL DEFAULT 0,
  `denda` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengembalian_peminjaman` (`peminjaman_id`),
  KEY `idx_pengembalian_user` (`user_id`),
  CONSTRAINT `fk_pengembalian_peminjaman`
    FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_pengembalian_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pengembalian`
(`id`,`peminjaman_id`,`tanggal_kembali`,`keterlambatan_hari`,`denda`,`user_id`)
VALUES
(1,1,'2026-08-08',0,0,1),
(2,2,'2026-08-07',0,0,1),
(3,3,'2026-08-10',0,0,1),
(4,4,'2026-08-07',0,0,1),
(5,5,'2026-08-12',0,0,1),
(6,6,'2026-08-13',0,0,1),
(7,7,'2026-08-12',0,0,1),
(8,8,'2026-08-12',0,0,1),
(9,9,'2026-08-16',0,0,1),
(10,10,'2026-08-17',0,0,1);

-- =========================================================
-- TABEL BOOKMARK
-- =========================================================

CREATE TABLE `bookmark` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `buku_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bookmark_user_buku` (`user_id`,`buku_id`),
  KEY `idx_bookmark_buku` (`buku_id`),
  CONSTRAINT `fk_bookmark_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_bookmark_buku`
    FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- TABEL NOTIFIKASI
-- =========================================================

CREATE TABLE `notifikasi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tipe` VARCHAR(30) NOT NULL DEFAULT 'info',
  `dibaca` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifikasi_user` (`user_id`,`dibaca`,`created_at`),
  CONSTRAINT `fk_notifikasi_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifikasi`
(`id`,`user_id`,`judul`,`pesan`,`tipe`,`dibaca`)
VALUES
(1,2,'Peminjaman sedang diproses',
 'Pengajuan peminjaman buku sedang menunggu persetujuan admin.',
 'info',0);

-- =========================================================
-- TABEL RATING BUKU
-- =========================================================

CREATE TABLE `rating_buku` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `buku_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rating_user_buku` (`user_id`,`buku_id`),
  KEY `idx_rating_buku` (`buku_id`),
  CONSTRAINT `fk_rating_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_rating_buku`
    FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `chk_rating`
    CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================
-- AUTO_INCREMENT
-- =========================================================

ALTER TABLE `anggota` AUTO_INCREMENT = 11;
ALTER TABLE `kategori` AUTO_INCREMENT = 11;
ALTER TABLE `penerbit` AUTO_INCREMENT = 11;
ALTER TABLE `buku` AUTO_INCREMENT = 12;
ALTER TABLE `user` AUTO_INCREMENT = 12;
ALTER TABLE `peminjaman` AUTO_INCREMENT = 12;
ALTER TABLE `peminjaman_detail` AUTO_INCREMENT = 12;
ALTER TABLE `pengembalian` AUTO_INCREMENT = 11;
ALTER TABLE `bookmark` AUTO_INCREMENT = 1;
ALTER TABLE `notifikasi` AUTO_INCREMENT = 2;
ALTER TABLE `rating_buku` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- LOGIN DEFAULT
-- =========================================================
-- Admin:
-- username : admin
-- password : admin123
--
-- User:
-- username : user1
-- password : 12345
-- anggota  : AG001 / Ahmad
--
-- User lain:
-- user2 s/d user10
-- password: 12345
-- =========================================================
