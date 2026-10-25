-- FUNCTION: tentukan status berdasarkan total bersih (pelanggaran - reward)
-- Ambang 25/50 taruh di SATU tempat ini saja -> gampang diubah kalau Guru BK revisi
DROP FUNCTION IF EXISTS fn_status_tindak_lanjut;
CREATE FUNCTION fn_status_tindak_lanjut(p_total_bersih INT)
RETURNS VARCHAR(25) DETERMINISTIC
BEGIN
  IF p_total_bersih >= 50 THEN RETURN 'perlu_tindak_lanjut';
  ELSEIF p_total_bersih >= 25 THEN RETURN 'perlu_perhatian';
  ELSE RETURN 'aman';
  END IF;
END;

-- FUNCTION: usia siswa (dipakai di tampilan data siswa)
DROP FUNCTION IF EXISTS fn_hitung_usia;
CREATE FUNCTION fn_hitung_usia(p_tgl_lahir DATE)
RETURNS INT DETERMINISTIC
BEGIN
  IF p_tgl_lahir IS NULL THEN RETURN NULL; END IF;
  RETURN TIMESTAMPDIFF(YEAR, p_tgl_lahir, CURDATE());
END;

-- PROCEDURE: hitung ulang rekap poin 1 siswa untuk 1 tahun ajaran
-- Dipanggil oleh trigger, baik saat poin baru DIINPUT maupun DIEDIT/DIHAPUS.
-- Ini yang memperbaiki bug lama: dulu cuma nambah pas INSERT, sekarang HITUNG ULANG PENUH.
DROP PROCEDURE IF EXISTS sp_rekap_poin_siswa;
CREATE PROCEDURE sp_rekap_poin_siswa(IN p_siswa_id BIGINT UNSIGNED, IN p_tahun_ajaran VARCHAR(12))
BEGIN
  DECLARE v_pelanggaran INT DEFAULT 0;
  DECLARE v_reward INT DEFAULT 0;
  DECLARE v_bersih INT DEFAULT 0;

  SELECT COALESCE(SUM(CASE WHEN tipe='pelanggaran' THEN poin ELSE 0 END),0),
         COALESCE(SUM(CASE WHEN tipe='reward' THEN poin ELSE 0 END),0)
    INTO v_pelanggaran, v_reward
    FROM poin_siswa
    WHERE siswa_id = p_siswa_id AND tahun_ajaran = p_tahun_ajaran;

  -- reward MENGURANGI pelanggaran (sesuai jawaban Guru BK), minimal 0
  SET v_bersih = GREATEST(v_pelanggaran - v_reward, 0);

  INSERT INTO poin_siswa_rekap (siswa_id, tahun_ajaran, total_pelanggaran, total_reward, total_bersih, status_tindak_lanjut)
  VALUES (p_siswa_id, p_tahun_ajaran, v_pelanggaran, v_reward, v_bersih, fn_status_tindak_lanjut(v_bersih))
  ON DUPLICATE KEY UPDATE
    total_pelanggaran = v_pelanggaran,
    total_reward = v_reward,
    total_bersih = v_bersih,
    status_tindak_lanjut = fn_status_tindak_lanjut(v_bersih);
END;

-- PROCEDURE: rekap absensi per kelas/bulan/tahun, PERBAIKAN bug lama (dulu NULL kalau siswa belum pernah absen)
DROP PROCEDURE IF EXISTS sp_rekap_absensi;
CREATE PROCEDURE sp_rekap_absensi(IN p_kelas_id BIGINT UNSIGNED, IN p_bulan INT, IN p_tahun INT)
BEGIN
  SELECT s.id AS siswa_id, s.nama,
    COALESCE(SUM(a.status='hadir'),0) AS hadir,
    COALESCE(SUM(a.status='sakit'),0) AS sakit,
    COALESCE(SUM(a.status='izin'),0) AS izin,
    COALESCE(SUM(a.status='alpa'),0) AS alpa
  FROM siswa s
  LEFT JOIN absensi a ON a.siswa_id = s.id
    AND MONTH(a.tanggal) = p_bulan AND YEAR(a.tanggal) = p_tahun
  WHERE s.kelas_id = p_kelas_id
  GROUP BY s.id, s.nama
  ORDER BY s.nama;
END;

-- TRIGGER: siswa pindah kelas butuh tahun_ajaran aktif saat poin diinput -> diisi dari kelas di app layer,
-- di sini kita cuma pastikan poin tidak negatif salah input
DROP TRIGGER IF EXISTS trg_before_insert_poin_siswa;
CREATE TRIGGER trg_before_insert_poin_siswa BEFORE INSERT ON poin_siswa
FOR EACH ROW
BEGIN
  IF NEW.poin < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Poin tidak boleh negatif';
  END IF;
END;

-- TRIGGER: setelah INSERT poin -> hitung ulang rekap (BUKAN cuma nambah)
DROP TRIGGER IF EXISTS trg_after_insert_poin_siswa;
CREATE TRIGGER trg_after_insert_poin_siswa AFTER INSERT ON poin_siswa
FOR EACH ROW
BEGIN
  CALL sp_rekap_poin_siswa(NEW.siswa_id, NEW.tahun_ajaran);
END;

-- TRIGGER BARU (perbaikan bug): setelah UPDATE poin -> hitung ulang rekap juga
-- Dulu tidak ada trigger ini sama sekali, jadi edit poin tidak pernah mengubah rekap.
DROP TRIGGER IF EXISTS trg_after_update_poin_siswa;
CREATE TRIGGER trg_after_update_poin_siswa AFTER UPDATE ON poin_siswa
FOR EACH ROW
BEGIN
  CALL sp_rekap_poin_siswa(NEW.siswa_id, NEW.tahun_ajaran);
  IF OLD.tahun_ajaran <> NEW.tahun_ajaran OR OLD.siswa_id <> NEW.siswa_id THEN
    CALL sp_rekap_poin_siswa(OLD.siswa_id, OLD.tahun_ajaran);
  END IF;
END;

-- TRIGGER BARU (perbaikan bug): setelah DELETE poin -> hitung ulang rekap juga
DROP TRIGGER IF EXISTS trg_after_delete_poin_siswa;
CREATE TRIGGER trg_after_delete_poin_siswa AFTER DELETE ON poin_siswa
FOR EACH ROW
BEGIN
  CALL sp_rekap_poin_siswa(OLD.siswa_id, OLD.tahun_ajaran);
END;

-- TRIGGER: validasi NIS unik (BUKAN auto-generate lagi, karena NIS diimpor dari data sekolah)
DROP TRIGGER IF EXISTS trg_before_insert_siswa;
CREATE TRIGGER trg_before_insert_siswa BEFORE INSERT ON siswa
FOR EACH ROW
BEGIN
  IF NEW.nis IS NULL OR NEW.nis = '' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NIS wajib diisi (diambil dari data sekolah)';
  END IF;
END;

-- TRIGGER: catat audit log saat data siswa diubah/dihapus (hanya kalau benar-benar ada perubahan)
DROP TRIGGER IF EXISTS trg_audit_update_siswa;
CREATE TRIGGER trg_audit_update_siswa AFTER UPDATE ON siswa
FOR EACH ROW
BEGIN
  IF NOT (OLD.nama <=> NEW.nama AND OLD.alamat <=> NEW.alamat AND OLD.status <=> NEW.status
          AND OLD.kelas_id <=> NEW.kelas_id AND OLD.nama_ortu <=> NEW.nama_ortu
          AND OLD.no_hp_ortu <=> NEW.no_hp_ortu) THEN
    INSERT INTO audit_log (tabel, record_id, aksi, data_lama, data_baru, user_id)
    VALUES ('siswa', NEW.id, 'update',
      JSON_OBJECT('nama',OLD.nama,'alamat',OLD.alamat,'status',OLD.status,'kelas_id',OLD.kelas_id,'nama_ortu',OLD.nama_ortu,'no_hp_ortu',OLD.no_hp_ortu),
      JSON_OBJECT('nama',NEW.nama,'alamat',NEW.alamat,'status',NEW.status,'kelas_id',NEW.kelas_id,'nama_ortu',NEW.nama_ortu,'no_hp_ortu',NEW.no_hp_ortu),
      @app_user_id);
  END IF;
END;

DROP TRIGGER IF EXISTS trg_audit_delete_siswa;
CREATE TRIGGER trg_audit_delete_siswa AFTER DELETE ON siswa
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (tabel, record_id, aksi, data_lama, user_id)
  VALUES ('siswa', OLD.id, 'delete', JSON_OBJECT('nama',OLD.nama,'nis',OLD.nis,'kelas_id',OLD.kelas_id), @app_user_id);
END;

-- TRIGGER: anekdot kategori berat -> set flag notifikasi wali (tanpa isi detail, sesuai jawaban Guru BK)
DROP TRIGGER IF EXISTS trg_after_insert_catatan_anekdot;
CREATE TRIGGER trg_after_insert_catatan_anekdot BEFORE INSERT ON catatan_anekdot
FOR EACH ROW
BEGIN
  IF NEW.kategori = 'berat' THEN
    SET NEW.notif_wali = 1;
  END IF;
END;