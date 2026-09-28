-- Jalankan SATU KALI jika database lama sudah terlanjur dibuat
-- tanpa kolom anggota_id pada tabel user.

ALTER TABLE `user`
    ADD COLUMN `anggota_id` INT(10) UNSIGNED NULL AFTER `level`;

ALTER TABLE `user`
    ADD UNIQUE KEY `uq_user_anggota` (`anggota_id`),
    ADD KEY `idx_user_anggota` (`anggota_id`);

UPDATE `user` SET `anggota_id` = 1 WHERE `id` = 2;
UPDATE `user` SET `anggota_id` = 2 WHERE `id` = 3;
UPDATE `user` SET `anggota_id` = 3 WHERE `id` = 4;
UPDATE `user` SET `anggota_id` = 4 WHERE `id` = 5;
UPDATE `user` SET `anggota_id` = 5 WHERE `id` = 6;
UPDATE `user` SET `anggota_id` = 6 WHERE `id` = 7;
UPDATE `user` SET `anggota_id` = 7 WHERE `id` = 8;
UPDATE `user` SET `anggota_id` = 8 WHERE `id` = 9;
UPDATE `user` SET `anggota_id` = 9 WHERE `id` = 10;
UPDATE `user` SET `anggota_id` = 10 WHERE `id` = 11;

ALTER TABLE `user`
    ADD CONSTRAINT `user_ibfk_1`
    FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
