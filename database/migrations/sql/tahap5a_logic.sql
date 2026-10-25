-- ======================================================
-- SI-BK Digital - Trigger & Procedure Fase 5 bagian A
-- Tanpa DELIMITER (cara PDO/Laravel), diawali DROP IF EXISTS supaya aman diulang
-- ======================================================

-- TRIGGER: kalau status siswa BERUBAH jadi 'alumni' -> otomatis dicatat di tabel alumni.
-- Tahun lulus diambil dari variabel sesi @tahun_lulus_app (diisi procedure kelulusan);
-- kalau kosong (misal status diubah manual lewat edit siswa), pakai tahun hari ini.
DROP TRIGGER IF EXISTS trg_after_update_siswa_status;
CREATE TRIGGER trg_after_update_siswa_status AFTER UPDATE ON siswa
FOR EACH ROW
BEGIN
  IF OLD.status <> 'alumni' AND NEW.status = 'alumni' THEN
    INSERT INTO alumni (siswa_id, tahun_lulus, keterangan, created_at, updated_at)
    VALUES (NEW.id, COALESCE(@tahun_lulus_app, YEAR(CURDATE())), 'Dicatat otomatis oleh trigger', NOW(), NOW());
  END IF;

  -- Kebalikannya: kalau status dikembalikan dari alumni (misal salah klik), catatan alumninya
  -- ikut dihapus supaya tabel alumni selalu cocok dengan status siswa.
  IF OLD.status = 'alumni' AND NEW.status <> 'alumni' THEN
    DELETE FROM alumni WHERE siswa_id = NEW.id;
  END IF;
END;

-- PROCEDURE: kelulusan 1 kelas (hanya tingkat 9 untuk SMP).
-- Semua siswa AKTIF di kelas itu diubah jadi 'alumni' dalam SATU transaksi:
-- kalau ada 1 saja yang gagal (misal data alumni dobel), SEMUANYA dibatalkan (ROLLBACK).
-- PENTING: procedure ini mengatur transaksinya sendiri, jadi JANGAN dipanggil dari dalam DB::transaction().
DROP PROCEDURE IF EXISTS sp_kelulusan_kelas;
CREATE PROCEDURE sp_kelulusan_kelas(
  IN p_kelas_id BIGINT UNSIGNED, IN p_tahun_lulus INT, OUT p_jumlah INT
)
BEGIN
  DECLARE v_tingkat INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET @tahun_lulus_app = NULL;
    RESIGNAL;
  END;

  SELECT tingkat INTO v_tingkat FROM kelas WHERE id = p_kelas_id;
  IF v_tingkat IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas tidak ditemukan';
  END IF;
  IF v_tingkat <> 9 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hanya kelas tingkat 9 yang bisa diluluskan';
  END IF;

  START TRANSACTION;
    SET @tahun_lulus_app = p_tahun_lulus;
    UPDATE siswa SET status = 'alumni' WHERE kelas_id = p_kelas_id AND status = 'aktif';
    SET p_jumlah = ROW_COUNT();
    SET @tahun_lulus_app = NULL;
    IF p_jumlah = 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tidak ada siswa aktif di kelas ini';
    END IF;
  COMMIT;
END;

-- PROCEDURE: kenaikan kelas. Semua siswa AKTIF di kelas asal dipindah ke kelas tujuan
-- (tingkat tujuan harus tepat 1 di atas tingkat asal), dalam satu transaksi.
DROP PROCEDURE IF EXISTS sp_kenaikan_kelas;
CREATE PROCEDURE sp_kenaikan_kelas(
  IN p_kelas_asal BIGINT UNSIGNED, IN p_kelas_tujuan BIGINT UNSIGNED, OUT p_jumlah INT
)
BEGIN
  DECLARE v_tingkat_asal INT;
  DECLARE v_tingkat_tujuan INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  SELECT tingkat INTO v_tingkat_asal FROM kelas WHERE id = p_kelas_asal;
  SELECT tingkat INTO v_tingkat_tujuan FROM kelas WHERE id = p_kelas_tujuan;

  IF v_tingkat_asal IS NULL OR v_tingkat_tujuan IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas asal atau tujuan tidak ditemukan';
  END IF;
  IF v_tingkat_tujuan <> v_tingkat_asal + 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas tujuan harus tepat satu tingkat di atas kelas asal';
  END IF;

  START TRANSACTION;
    UPDATE siswa SET kelas_id = p_kelas_tujuan WHERE kelas_id = p_kelas_asal AND status = 'aktif';
    SET p_jumlah = ROW_COUNT();
    IF p_jumlah = 0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tidak ada siswa aktif di kelas asal';
    END IF;
  COMMIT;
END;
