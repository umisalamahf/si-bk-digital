-- ======================================================
-- SI-BK Digital - Tabel Fase 3 (Asesmen BK + Persuratan)
-- Pilot 2 jenis asesmen dulu: Gaya Belajar & Bakat Minat
-- (sesuai saran MVP di spesifikasi -- DCM/Sosiometri butuh
-- model skoring beda, menyusul setelah contoh instrumen asli dikirim Guru BK)
-- ======================================================

CREATE TABLE asesmen_jenis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE asesmen_instrumen (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asesmen_jenis_id BIGINT UNSIGNED NOT NULL,
  nama_instrumen VARCHAR(150) NOT NULL,
  jenjang VARCHAR(50),
  keterangan TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (asesmen_jenis_id) REFERENCES asesmen_jenis(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE asesmen_butir (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id BIGINT UNSIGNED NOT NULL,
  nomor INT NOT NULL,
  pertanyaan TEXT NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  bobot INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (instrumen_id) REFERENCES asesmen_instrumen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE asesmen_link (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrumen_id BIGINT UNSIGNED NOT NULL,
  kelas_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  kadaluarsa DATETIME NOT NULL,
  aktif BOOLEAN NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (instrumen_id) REFERENCES asesmen_instrumen(id) ON DELETE RESTRICT,
  FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE asesmen_jawaban (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  siswa_id BIGINT UNSIGNED NOT NULL,
  instrumen_id BIGINT UNSIGNED NOT NULL,
  tanggal_isi DATETIME NOT NULL,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  UNIQUE(siswa_id, instrumen_id),
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  FOREIGN KEY (instrumen_id) REFERENCES asesmen_instrumen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE asesmen_jawaban_detail (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jawaban_id BIGINT UNSIGNED NOT NULL,
  butir_id BIGINT UNSIGNED NOT NULL,
  nilai_jawaban INT NOT NULL,
  FOREIGN KEY (jawaban_id) REFERENCES asesmen_jawaban(id) ON DELETE CASCADE,
  FOREIGN KEY (butir_id) REFERENCES asesmen_butir(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE asesmen_hasil (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jawaban_id BIGINT UNSIGNED NOT NULL UNIQUE,
  kategori_dominan_1 VARCHAR(50),
  kategori_dominan_2 VARCHAR(50),
  kategori_dominan_3 VARCHAR(50),
  skor_total INT,
  kesimpulan TEXT,
  rekomendasi TEXT,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (jawaban_id) REFERENCES asesmen_jawaban(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jenis_surat (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,
  nama_surat VARCHAR(150) NOT NULL,
  template LONGTEXT,
  urutan_terakhir INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE surat (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jenis_surat_id BIGINT UNSIGNED NOT NULL,
  siswa_id BIGINT UNSIGNED NOT NULL,
  guru_bk_id BIGINT UNSIGNED NOT NULL,
  nomor_surat VARCHAR(100) NOT NULL UNIQUE,
  tanggal DATE NOT NULL,
  perihal VARCHAR(200),
  isi LONGTEXT,
  status ENUM('draft','dicetak') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (jenis_surat_id) REFERENCES jenis_surat(id) ON DELETE RESTRICT,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
  FOREIGN KEY (guru_bk_id) REFERENCES guru_bk(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
