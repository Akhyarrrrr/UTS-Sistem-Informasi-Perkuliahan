-- Struktur aktual sistem UTS; MySQL 8.0.46
SET NAMES utf8mb4;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','dosen','mahasiswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mahasiswa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fakultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fakultas_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prodi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fakultas_id` bigint unsigned NOT NULL,
  `kode` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batas_sks` tinyint unsigned NOT NULL DEFAULT '24',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prodi_kode_unique` (`kode`),
  KEY `prodi_fakultas_id_foreign` (`fakultas_id`),
  CONSTRAINT `prodi_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_prodi_0` CHECK ((`batas_sks` between 1 and 30))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mahasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `npm` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `angkatan` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswa_npm_unique` (`npm`),
  UNIQUE KEY `mahasiswa_user_id_unique` (`user_id`),
  KEY `mahasiswa_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `mahasiswa_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mahasiswa_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dosen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `kode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dosen_kode_unique` (`kode`),
  UNIQUE KEY `dosen_user_id_unique` (`user_id`),
  KEY `dosen_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `dosen_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `dosen_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `matakuliah` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` bigint unsigned NOT NULL,
  `kode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sks` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matakuliah_kode_unique` (`kode`),
  KEY `matakuliah_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `matakuliah_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_matakuliah_0` CHECK ((`sks` between 1 and 6))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `periode` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_mulai` smallint unsigned NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_unicode_ci NOT NULL,
  `krs_mulai` date NOT NULL,
  `krs_selesai` date NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `periode_nama_unique` (`nama`),
  CONSTRAINT `chk_periode_0` CHECK ((`krs_mulai` <= `krs_selesai`))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ruang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruang_kode_unique` (`kode`),
  CONSTRAINT `chk_ruang_0` CHECK ((`kapasitas` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `matakuliah_id` bigint unsigned NOT NULL,
  `periode_id` bigint unsigned NOT NULL,
  `dosen_id` bigint unsigned NOT NULL,
  `kode` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` smallint unsigned NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `first_published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kelas_matakuliah_id_periode_id_kode_unique` (`matakuliah_id`,`periode_id`,`kode`),
  KEY `kelas_periode_id_foreign` (`periode_id`),
  KEY `kelas_dosen_id_foreign` (`dosen_id`),
  CONSTRAINT `kelas_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kelas_matakuliah_id_foreign` FOREIGN KEY (`matakuliah_id`) REFERENCES `matakuliah` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kelas_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_kelas_0` CHECK ((`kapasitas` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jadwal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `ruang_id` bigint unsigned DEFAULT NULL,
  `hari` tinyint unsigned NOT NULL,
  `mulai` time NOT NULL,
  `selesai` time NOT NULL,
  `mode` enum('Luring','Daring') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Luring',
  `tautan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_kelas_id_foreign` (`kelas_id`),
  KEY `jadwal_ruang_id_foreign` (`ruang_id`),
  CONSTRAINT `jadwal_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `jadwal_ruang_id_foreign` FOREIGN KEY (`ruang_id`) REFERENCES `ruang` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_jadwal_0` CHECK ((`hari` between 1 and 7)),
  CONSTRAINT `chk_jadwal_1` CHECK ((`mulai` < `selesai`)),
  CONSTRAINT `chk_jadwal_2` CHECK (((`mode` = _utf8mb4'Daring') or (`ruang_id` is not null)))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `krs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `periode_id` bigint unsigned NOT NULL,
  `status` enum('draf','diajukan','disetujui','dikembalikan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draf',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_mahasiswa_id_periode_id_unique` (`mahasiswa_id`,`periode_id`),
  KEY `krs_periode_id_foreign` (`periode_id`),
  CONSTRAINT `krs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `krs_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `krs_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_id` bigint unsigned NOT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_detail_krs_id_kelas_id_unique` (`krs_id`,`kelas_id`),
  KEY `krs_detail_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `krs_detail_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `krs_detail_krs_id_foreign` FOREIGN KEY (`krs_id`) REFERENCES `krs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `komponen_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `nama` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `komponen_nilai_kelas_id_nama_unique` (`kelas_id`,`nama`),
  CONSTRAINT `komponen_nilai_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_komponen_nilai_0` CHECK (((`bobot` > 0) and (`bobot` <= 100)))
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nilai_komponen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_detail_id` bigint unsigned NOT NULL,
  `komponen_nilai_id` bigint unsigned NOT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nilai_komponen_krs_detail_id_komponen_nilai_id_unique` (`krs_detail_id`,`komponen_nilai_id`),
  KEY `nilai_komponen_komponen_nilai_id_foreign` (`komponen_nilai_id`),
  CONSTRAINT `nilai_komponen_komponen_nilai_id_foreign` FOREIGN KEY (`komponen_nilai_id`) REFERENCES `komponen_nilai` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `nilai_komponen_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_nilai_komponen_0` CHECK (((`nilai` is null) or (`nilai` between 0 and 100)))
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `skala_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_id` bigint unsigned NOT NULL,
  `huruf` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimum` decimal(5,2) NOT NULL,
  `angka` decimal(3,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skala_nilai_periode_id_huruf_unique` (`periode_id`,`huruf`),
  UNIQUE KEY `skala_nilai_periode_id_minimum_unique` (`periode_id`,`minimum`),
  CONSTRAINT `skala_nilai_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_skala_nilai_0` CHECK ((`minimum` between 0 and 100)),
  CONSTRAINT `chk_skala_nilai_1` CHECK ((`angka` between 0 and 4))
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pertemuan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `nomor` tinyint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `topik` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pertemuan_kelas_id_nomor_unique` (`kelas_id`,`nomor`),
  CONSTRAINT `pertemuan_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_pertemuan_0` CHECK ((`nomor` between 1 and 32))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `presensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pertemuan_id` bigint unsigned NOT NULL,
  `krs_detail_id` bigint unsigned NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpa') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `presensi_pertemuan_id_krs_detail_id_unique` (`pertemuan_id`,`krs_detail_id`),
  KEY `presensi_krs_detail_id_foreign` (`krs_detail_id`),
  CONSTRAINT `presensi_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `presensi_pertemuan_id_foreign` FOREIGN KEY (`pertemuan_id`) REFERENCES `pertemuan` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `aksi` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entitas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint unsigned DEFAULT NULL,
  `detail` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_log_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$
CREATE TRIGGER `check_krs_detail_insert` BEFORE INSERT ON `krs_detail` FOR EACH ROW BEGIN IF (SELECT periode_id FROM kelas WHERE id=NEW.kelas_id) <> (SELECT periode_id FROM krs WHERE id=NEW.krs_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Periode kelas harus sama dengan KRS'; END IF; END$$

CREATE TRIGGER `validate_krs_detail_update` BEFORE UPDATE ON `krs_detail` FOR EACH ROW BEGIN IF (SELECT periode_id FROM krs WHERE id=NEW.krs_id) <> (SELECT periode_id FROM kelas WHERE id=NEW.kelas_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hubungan akademik tidak sesuai'; END IF; END$$

CREATE TRIGGER `check_nilai_insert` BEFORE INSERT ON `nilai_komponen` FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM komponen_nilai WHERE id=NEW.komponen_nilai_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Komponen nilai harus berasal dari kelas peserta'; END IF; END$$

CREATE TRIGGER `validate_nilai_komponen_update` BEFORE UPDATE ON `nilai_komponen` FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM komponen_nilai WHERE id=NEW.komponen_nilai_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hubungan akademik tidak sesuai'; END IF; END$$

CREATE TRIGGER `check_presensi_insert` BEFORE INSERT ON `presensi` FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM pertemuan WHERE id=NEW.pertemuan_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Presensi harus berasal dari kelas peserta'; END IF; END$$

CREATE TRIGGER `validate_presensi_update` BEFORE UPDATE ON `presensi` FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM pertemuan WHERE id=NEW.pertemuan_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hubungan akademik tidak sesuai'; END IF; END$$

DELIMITER ;
