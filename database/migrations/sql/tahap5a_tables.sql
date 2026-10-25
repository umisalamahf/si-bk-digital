-- ======================================================
-- SI-BK Digital - Tabel Fase 5 bagian A
-- Home Visit, Jadwal Layanan, Kalender Agenda, Alumni
-- ======================================================

CREATE TABLE home_visit (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  alamat_dikunjungi VARCHAR(255),
  tujuan VARCHAR(255) NOT NULL,
  hasil TEXT,
  tindak_lanjut TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE jadwal_layanan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  kelas_id BIGINT UNSIGNED NULL,
  siswa_id BIGINT UNSIGNED NULL,
  tanggal DATE NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  jenis_layanan VARCHAR(100) NOT NULL,
  tempat VARCHAR(100),
  keterangan TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE RESTRICT,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE kalender_agenda (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  judul VARCHAR(200) NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  deskripsi TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabel alumni DIISI OTOMATIS oleh trigger trg_after_update_siswa_status,
-- jangan diisi manual dari aplikasi.
CREATE TABLE alumni (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL UNIQUE,
  tahun_lulus YEAR NOT NULL,
  keterangan VARCHAR(255),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
