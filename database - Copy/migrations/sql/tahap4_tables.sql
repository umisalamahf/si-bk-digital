-- ======================================================
-- SI-BK Digital - Tabel Fase 4 (Program BK, RPL, Evaluasi, Jurnal)
-- ======================================================

CREATE TABLE program_bk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  tahun_ajaran VARCHAR(12) NOT NULL,
  capaian_layanan TEXT,
  alur_tujuan TEXT,
  action_plan TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE rpl (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  program_bk_id BIGINT UNSIGNED NOT NULL,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  kelas_id BIGINT UNSIGNED NULL,
  jenis_rpl ENUM('individual','kelompok','klasikal','konseling_individual','konseling_kelompok') NOT NULL,
  topik VARCHAR(200) NOT NULL,
  tanggal DATE NOT NULL,
  tujuan TEXT,
  materi TEXT,
  masalah TEXT,
  teknik TEXT,
  tindak_lanjut TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (program_bk_id) REFERENCES program_bk(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE rpl_siswa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rpl_id BIGINT UNSIGNED NOT NULL,
  siswa_id BIGINT UNSIGNED NOT NULL,
  UNIQUE(rpl_id, siswa_id),
  FOREIGN KEY (rpl_id) REFERENCES rpl(id) ON DELETE CASCADE,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE evaluasi_layanan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rpl_id BIGINT UNSIGNED NOT NULL,
  jenis_evaluasi ENUM('laiseg','laijapen','laijapan') NOT NULL,
  tanggal DATE NOT NULL,
  hasil TEXT,
  tindak_lanjut TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (rpl_id) REFERENCES rpl(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jurnal_layanan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  kegiatan TEXT NOT NULL,
  sasaran VARCHAR(200),
  hasil TEXT,
  keterangan TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
