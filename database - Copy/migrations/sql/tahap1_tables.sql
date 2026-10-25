-- ======================================================
-- SI-BK Digital - Tabel Tahap 1 (Fondasi + Modul Inti)
-- Mengikuti ERD_SI-BK_Digital-v1.md, dengan penyesuaian:
--  - siswa.status ditambah 'tidak_aktif' (siswa pindah/keluar)
--  - NIS TIDAK auto-generate (diimpor dari data sekolah),
--    trigger cuma validasi, bukan membuat nomor sendiri
-- ======================================================

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','guru_bk','wali_kelas','kepala_sekolah') NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE sekolah (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  npsn VARCHAR(20) UNIQUE,
  nama_sekolah VARCHAR(150) NOT NULL,
  alamat TEXT,
  kepala_sekolah VARCHAR(150),
  logo VARCHAR(255),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE guru_bk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  nip VARCHAR(30),
  no_hp VARCHAR(20),
  foto VARCHAR(255),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE guru_wali (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  nama VARCHAR(150) NOT NULL,
  nip VARCHAR(30),
  no_hp VARCHAR(20),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE kelas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sekolah_id BIGINT UNSIGNED NOT NULL,
  guru_wali_id BIGINT UNSIGNED NULL,
  nama_kelas VARCHAR(50) NOT NULL,
  tingkat TINYINT NOT NULL,
  tahun_ajaran VARCHAR(12) NOT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (sekolah_id) REFERENCES sekolah(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_wali_id) REFERENCES guru_wali(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE siswa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kelas_id BIGINT UNSIGNED NOT NULL,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nisn VARCHAR(20) UNIQUE,
  nama VARCHAR(150) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  tempat_lahir VARCHAR(100),
  tanggal_lahir DATE,
  alamat TEXT,
  nama_ortu VARCHAR(150),
  no_hp_ortu VARCHAR(20),
  status ENUM('aktif','tidak_aktif','alumni') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE absensi (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('hadir','sakit','izin','alpa') NOT NULL,
  keterangan VARCHAR(255),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  UNIQUE(siswa_id, tanggal),
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE catatan_anekdot (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  kejadian TEXT NOT NULL,
  kategori ENUM('ringan','sedang','berat') NOT NULL,
  tindak_lanjut TEXT,
  notif_wali BOOLEAN NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE jenis_pelanggaran (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) UNIQUE,
  nama_pelanggaran VARCHAR(150) NOT NULL,
  kategori ENUM('ringan','sedang','berat') NOT NULL,
  poin INT NOT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE jenis_reward (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) UNIQUE,
  nama_reward VARCHAR(150) NOT NULL,
  poin INT NOT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE poin_siswa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  tipe ENUM('pelanggaran','reward') NOT NULL,
  jenis_pelanggaran_id BIGINT UNSIGNED NULL,
  jenis_reward_id BIGINT UNSIGNED NULL,
  tahun_ajaran VARCHAR(12) NOT NULL,
  tanggal DATE NOT NULL,
  poin INT NOT NULL,
  keterangan VARCHAR(255),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT,
  FOREIGN KEY (jenis_pelanggaran_id) REFERENCES jenis_pelanggaran(id) ON DELETE RESTRICT,
  FOREIGN KEY (jenis_reward_id) REFERENCES jenis_reward(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- rekap kini per (siswa, tahun_ajaran) karena poin direset tiap tahun ajaran (sementara)
CREATE TABLE poin_siswa_rekap (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  tahun_ajaran VARCHAR(12) NOT NULL,
  total_pelanggaran INT NOT NULL DEFAULT 0,
  total_reward INT NOT NULL DEFAULT 0,
  total_bersih INT NOT NULL DEFAULT 0,
  status_tindak_lanjut ENUM('aman','perlu_perhatian','perlu_tindak_lanjut') NOT NULL DEFAULT 'aman',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  UNIQUE(siswa_id, tahun_ajaran),
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tabel VARCHAR(50) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  aksi ENUM('update','delete') NOT NULL,
  data_lama JSON NULL,
  data_baru JSON NULL,
  user_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
