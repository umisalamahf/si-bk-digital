# Catatan implementasi: angket kepuasan dan rekap layanan

- Angket ditautkan ke RPL yang sudah ada; tautan acak berlaku 90 hari, disimpan sebagai hash, dan dapat diisi lebih dari sekali. Responden tidak diminta nama. Kategori yang tersedia: peserta didik, orang tua, kepala sekolah, dan guru.
- Lima pernyataan umum memakai skor 1–4 dengan label persis STS, TS, S, SS. Rerata dan persentase dihitung per butir; persentase adalah rerata dibagi skor maksimum (4), dikali 100. Pernyataan awal bersifat generik agar dapat dipakai lintas jenis layanan.
- RPL yang ada tetap menjadi rencana/PDF. Rekap pelaksanaan aktual disimpan terpisah per RPL; laporan memisahkan konseling individual, konseling kelompok, bimbingan kelompok (`kelompok`), dan layanan klasikal.
- Tautan angket bersifat publik bagi pemegang token; halaman hasil, rekap, dan penyimpanan pelaksanaan dibatasi untuk Guru BK pemilik RPL. Migration baru bersifat aditif dan tidak dijalankan otomatis oleh perubahan ini.
