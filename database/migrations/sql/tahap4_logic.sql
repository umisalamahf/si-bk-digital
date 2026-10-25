-- ======================================================
-- SI-BK Digital - Trigger Fase 4
-- Menegaskan aturan "Isi RPL per Jenis" (ERD bagian 3) di level database,
-- bukan cuma di Laravel -- jadi kalau ada jalur lain (phpMyAdmin dll),
-- tetap ketangkep kalau masalah/teknik kosong padahal wajib.
-- ======================================================

DROP TRIGGER IF EXISTS trg_before_insert_rpl;
CREATE TRIGGER trg_before_insert_rpl BEFORE INSERT ON rpl
FOR EACH ROW
BEGIN
  IF NEW.jenis_rpl IN ('konseling_individual', 'konseling_kelompok')
     AND (NEW.masalah IS NULL OR NEW.masalah = '') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RPL jenis konseling wajib mengisi kolom masalah';
  END IF;

  IF NEW.jenis_rpl = 'klasikal' AND NEW.kelas_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RPL klasikal wajib memilih kelas';
  END IF;
END;

DROP TRIGGER IF EXISTS trg_before_update_rpl;
CREATE TRIGGER trg_before_update_rpl BEFORE UPDATE ON rpl
FOR EACH ROW
BEGIN
  IF NEW.jenis_rpl IN ('konseling_individual', 'konseling_kelompok')
     AND (NEW.masalah IS NULL OR NEW.masalah = '') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RPL jenis konseling wajib mengisi kolom masalah';
  END IF;
END;
