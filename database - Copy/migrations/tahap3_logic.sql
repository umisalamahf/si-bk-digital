-- ======================================================
-- SI-BK Digital - Function/Procedure Fase 3
-- TANPA JSON_TABLE (biar jalan di MariaDB 10.4, sesuai keputusan Fase 1)
-- ======================================================

-- FUNCTION: cek siswa sudah isi instrumen ini atau belum (cegah isi 2x)
DROP FUNCTION IF EXISTS fn_cek_sudah_isi_asesmen;
CREATE FUNCTION fn_cek_sudah_isi_asesmen(p_siswa_id BIGINT UNSIGNED, p_instrumen_id BIGINT UNSIGNED)
RETURNS BOOLEAN DETERMINISTIC
BEGIN
  RETURN EXISTS(SELECT 1 FROM asesmen_jawaban WHERE siswa_id = p_siswa_id AND instrumen_id = p_instrumen_id);
END;

-- FUNCTION: format nomor surat jadi "421.5/005/SMP/IX/2026"
DROP FUNCTION IF EXISTS fn_format_nomor_surat;
CREATE FUNCTION fn_format_nomor_surat(p_kode VARCHAR(10), p_urutan INT, p_bulan INT, p_tahun INT)
RETURNS VARCHAR(100) DETERMINISTIC
BEGIN
  DECLARE v_romawi VARCHAR(4);
  SET v_romawi = ELT(p_bulan, 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
  RETURN CONCAT(p_kode, '/', LPAD(p_urutan, 3, '0'), '/', v_romawi, '/', p_tahun);
END;

-- PROCEDURE: bikin token link asesmen untuk 1 kelas (masa berlaku dalam hari)
DROP PROCEDURE IF EXISTS sp_generate_link_asesmen;
CREATE PROCEDURE sp_generate_link_asesmen(
  IN p_instrumen_id BIGINT UNSIGNED, IN p_kelas_id BIGINT UNSIGNED, IN p_masa_berlaku_hari INT,
  OUT p_token VARCHAR(64)
)
BEGIN
  -- token dibuat dari SHA2 + waktu sekarang, cukup acak buat skala 1 sekolah
  SET p_token = SHA2(CONCAT(p_instrumen_id, p_kelas_id, NOW(6), RAND()), 256);
  INSERT INTO asesmen_link (instrumen_id, kelas_id, token, kadaluarsa, aktif)
  VALUES (p_instrumen_id, p_kelas_id, p_token, DATE_ADD(NOW(), INTERVAL p_masa_berlaku_hari DAY), 1);
END;

-- PROCEDURE: hitung hasil asesmen KATEGORIAL (skor per kategori -> ambil yang paling dominan)
-- Cocok untuk Gaya Belajar & Bakat Minat. DCM/Sosiometri BEDA model, menyusul terpisah.
-- Kalau skor SERI di posisi 1, keduanya ditulis (sesuai jawaban Guru BK).
DROP PROCEDURE IF EXISTS sp_hitung_hasil_asesmen;
CREATE PROCEDURE sp_hitung_hasil_asesmen(IN p_jawaban_id BIGINT UNSIGNED)
BEGIN
  DECLARE v_kat1 VARCHAR(50) DEFAULT NULL;
  DECLARE v_kat2 VARCHAR(50) DEFAULT NULL;
  DECLARE v_kat3 VARCHAR(50) DEFAULT NULL;
  DECLARE v_total INT DEFAULT 0;
  DECLARE done INT DEFAULT 0;
  DECLARE v_kategori VARCHAR(50);
  DECLARE v_skor INT;
  DECLARE v_rank INT DEFAULT 0;
  DECLARE v_skor_sebelumnya INT DEFAULT NULL;

  -- Tabel sementara: total skor per kategori untuk 1 jawaban ini, urut dari terbesar
  DROP TEMPORARY TABLE IF EXISTS tmp_skor_kategori;
  CREATE TEMPORARY TABLE tmp_skor_kategori AS
    SELECT b.kategori, SUM(d.nilai_jawaban * b.bobot) AS skor
    FROM asesmen_jawaban_detail d
    JOIN asesmen_butir b ON b.id = d.butir_id
    WHERE d.jawaban_id = p_jawaban_id
    GROUP BY b.kategori
    ORDER BY skor DESC;

  SELECT COALESCE(SUM(skor),0) INTO v_total FROM tmp_skor_kategori;

  -- Ambil kategori dengan RANKING (seri = ranking sama, semua ditulis)
  BEGIN
    DECLARE cur CURSOR FOR SELECT kategori, skor FROM tmp_skor_kategori ORDER BY skor DESC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN cur;
    read_loop: LOOP
      FETCH cur INTO v_kategori, v_skor;
      IF done THEN LEAVE read_loop; END IF;

      IF v_skor_sebelumnya IS NULL OR v_skor <> v_skor_sebelumnya THEN
        SET v_rank = v_rank + 1;
      END IF;
      SET v_skor_sebelumnya = v_skor;

      IF v_rank = 1 THEN
        SET v_kat1 = IF(v_kat1 IS NULL, v_kategori, CONCAT(v_kat1, ' & ', v_kategori));
      ELSEIF v_rank = 2 THEN
        SET v_kat2 = IF(v_kat2 IS NULL, v_kategori, CONCAT(v_kat2, ' & ', v_kategori));
      ELSEIF v_rank = 3 THEN
        SET v_kat3 = IF(v_kat3 IS NULL, v_kategori, CONCAT(v_kat3, ' & ', v_kategori));
      END IF;
    END LOOP;
    CLOSE cur;
  END;

  DROP TEMPORARY TABLE IF EXISTS tmp_skor_kategori;

  INSERT INTO asesmen_hasil (jawaban_id, kategori_dominan_1, kategori_dominan_2, kategori_dominan_3, skor_total, kesimpulan, rekomendasi)
  VALUES (
    p_jawaban_id, v_kat1, v_kat2, v_kat3, v_total,
    CONCAT('Kategori dominan: ', COALESCE(v_kat1, '-')),
    -- Rekomendasi masih PLACEHOLDER -- menunggu kalimat baku dari Guru BK (pertanyaan nomor 11)
    CONCAT('[Rekomendasi untuk kategori ', COALESCE(v_kat1,'-'), ' menyusul dari Guru BK]')
  )
  ON DUPLICATE KEY UPDATE
    kategori_dominan_1 = v_kat1, kategori_dominan_2 = v_kat2, kategori_dominan_3 = v_kat3,
    skor_total = v_total,
    kesimpulan = CONCAT('Kategori dominan: ', COALESCE(v_kat1, '-'));
END;

-- PROCEDURE: generate nomor surat ANTI BENTROK (pakai FOR UPDATE di dalam transaksi)
DROP PROCEDURE IF EXISTS sp_generate_nomor_surat;
CREATE PROCEDURE sp_generate_nomor_surat(IN p_jenis_surat_id BIGINT UNSIGNED, OUT p_nomor VARCHAR(100))
BEGIN
  DECLARE v_kode VARCHAR(10);
  DECLARE v_urutan INT;

  -- SELECT ... FOR UPDATE: kalau 2 surat dibuat BERSAMAAN, yang kedua nunggu dulu
  -- sampai yang pertama selesai, supaya nomornya TIDAK BENTROK.
  SELECT kode, urutan_terakhir + 1 INTO v_kode, v_urutan
  FROM jenis_surat WHERE id = p_jenis_surat_id FOR UPDATE;

  UPDATE jenis_surat SET urutan_terakhir = v_urutan WHERE id = p_jenis_surat_id;

  -- urutan TIDAK reset tiap tahun (sesuai jawaban Guru BK), jadi tidak perlu cek tahun di sini
  SET p_nomor = fn_format_nomor_surat(v_kode, v_urutan, MONTH(CURDATE()), YEAR(CURDATE()));
END;
