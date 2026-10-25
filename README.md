# SI-BK Digital

Sistem Informasi Administrasi Bimbingan dan Konseling berbasis web untuk membantu Guru BK mengelola data siswa dan administrasi layanan secara terintegrasi.

## Teknologi

- Laravel 12 dan PHP 8.2+
- Blade, HTML, Tailwind CSS CDN, dan JavaScript
- MySQL atau MariaDB (migration menggunakan stored procedure, function, dan trigger)
- DomPDF untuk dokumen PDF

## Modul yang tersedia

- Dashboard dan identitas sekolah.
- Data kelas, siswa, wali kelas, dan alumni.
- Import roster siswa tahun ajaran 2026/2027 dari `storage_data/Absen jadi 2026-2027.xlsx` menggunakan `php artisan db:seed --class=ImportStudents2026_2027Seeder`. Seeder membaca sheet KELAS 7, KELAS 8, dan KELAS 9 dan menghubungkan siswa ke kelas sesuai jenjang. Baris yang tidak memiliki Nomor Induk mendapat NIS sementara berawalan `TMP-`; ganti dengan NIS resmi saat tersedia.
- Untuk menghapus enam siswa dummy bawaan tanpa menghapus roster Excel, jalankan `php artisan db:seed --class=CleanupDummyStudentsSeeder`. Seeder hanya menghapus identitas dummy yang NIS dan namanya cocok persis, beserta catatan terkait siswa tersebut; struktur kelas dan siswa Excel tetap dipertahankan.
- Absensi kelas, rekap bulanan, dan cetak PDF.
- Catatan anekdot, master kode pelanggaran, pencatatan poin pelanggaran/reward dengan bobot otomatis, riwayat poin, dan rekap PDF.
- Asesmen melalui link/QR, penyimpanan jawaban, hasil skoring, dan rekap PDF.
- Instrumen DCM dan Sosiometri, serta Motivasi Belajar, Kebiasaan Belajar, Penyesuaian Diri, Perkembangan Sosial, dan Perencanaan Karier SMP dengan skoring sesuai pedoman dokumen.
- Formulir privat Guru BK untuk observasi murid, wawancara, konsultasi, dan mediasi konflik.
- Pengelolaan jenis instrumen dan butir pertanyaan asesmen.
- Program BK dan lima jenis RPL, evaluasi layanan, serta cetak RPL.
- RPL Bimbingan Klasikal dengan identitas umum, identifikasi, desain pembelajaran, pengalaman belajar, asesmen, dan pengesahan dua pihak. Identitas Kepala Sekolah diambil dari Identitas Sekolah, sedangkan Guru BK dari profil Guru BK.
- Jadwal layanan, agenda BK, home visit, dan jurnal layanan.
- Persuratan BK, termasuk Surat Kelakuan Baik dengan nomor otomatis dan PDF siap cetak.
- Bank dokumen Instrumen BK.
- Pengelolaan akun dan audit log.

Modul asesmen mengikuti butir dan pedoman skoring yang diberikan sekolah. Instrumen contoh lain yang sudah ada seperti AKPD, Gaya Belajar, dan Tes Bakat Minat tetap menggunakan skoring kategori; DCM menggunakan jawaban Ya/Tidak dan rekap masalah per kategori. Motivasi Belajar, Kebiasaan Belajar, Penyesuaian Diri, Perkembangan Sosial, dan Perencanaan Karier SMP memakai skoring empat opsi, pembalikan butir negatif, rentang kategori, dan ringkasan skor per aspek dari pedoman. Sosiometri menyimpan tiga pilihan berurutan pada setiap butir pilihan, respons terbuka, dan rekap frekuensi nominasi. Interpretasi hasil tetap perlu ditinjau Guru BK dan tidak menggantikan penilaian profesional.

## Prasyarat

- PHP 8.2 atau lebih baru beserta ekstensi Laravel yang diperlukan.
- Composer.
- MySQL/MariaDB yang mendukung foreign key, stored procedure, function, dan trigger.
- Node.js dan npm jika ingin menjalankan build frontend.

## Deploy ke Render

Panduan Docker, environment variables, database MySQL/MariaDB eksternal, persistent disk untuk dokumen privat, dan langkah verifikasi tersedia di [docs/DEPLOY-RENDER.md](docs/DEPLOY-RENDER.md). Jangan commit `.env` atau file roster siswa.

## Menjalankan secara lokal

1. Siapkan database MySQL/MariaDB kosong dan salin `.env.example` menjadi `.env`.
2. Atur koneksi database di `.env`:

   ```dotenv
   APP_NAME="SI-BK Digital"
   APP_LOCALE=id
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sibk_digital
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Pasang dependency dan siapkan application key:

   ```powershell
   composer install
   php artisan key:generate
   ```

4. Jalankan migration dan data demonstrasi:

   ```powershell
   php artisan migrate
   php artisan db:seed
   ```

   Migration tambahan untuk bank instrumen, jenis surat Kelakuan Baik, pilihan sosiometri, instrumen asesmen baru, dan arsip formulir layanan BK dijalankan bersama migration lainnya. Tidak perlu menjalankan SQL secara manual.

5. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

   Buka alamat yang ditampilkan Artisan. Untuk membangun aset frontend bila diperlukan:

   ```powershell
   npm install
   npm run build
   ```

Dokumen Instrumen BK disimpan pada disk privat Laravel, bukan di folder yang dapat diakses langsung melalui URL publik. Unduh dokumen melalui halaman aplikasi setelah login.

## Akun demonstrasi

Seeder menyediakan akun berikut dengan kata sandi awal `password123`:

| Peran | Email |
|---|---|
| Admin | `admin@sibk.test` |
| Guru BK | `gurubk@sibk.test` |
| Kepala Sekolah | `kepsek@sibk.test` |

Ganti kata sandi demonstrasi dan data sekolah/siswa sebelum aplikasi digunakan dengan data sebenarnya.

## Tes

Jalankan tes otomatis menggunakan:

```powershell
php artisan test
```

Migration utama menggunakan SQL MySQL/MariaDB; SQLite tidak dapat menjalankan seluruh fitur database seperti procedure, function, dan trigger. Untuk menguji alur aplikasi secara menyeluruh, gunakan database MySQL/MariaDB yang disiapkan khusus untuk pengujian.

## Catatan pengembangan

- Data dan relasi database yang ada dipertahankan; perubahan skema baru dibuat melalui migration tambahan.
- Perluasan format RPL bersifat aditif melalui migration `2026_10_25_000012_expand_rpl_classical_template`; form klasikal mensyaratkan field format baru, sementara jenis RPL yang lain mempertahankan alur validasi sebelumnya.
- Master pelanggaran menampilkan `kode_pelanggaran` dan `bobot_point` sebagai kolom generated yang kompatibel dengan kolom lama `kode` dan `poin`; pencatatan poin serta trigger lama tetap memakai kolom lama.
- Sebanyak 35 kode pelanggaran objektif dari daftar SMPN 2 dan 3 Dayeuhkolot serta rujukan tindak lanjut akumulasi disediakan melalui seeder `DaftarKodePelanggaranResmiSeeder`. Kriteria sumber yang menarget orientasi seksual tidak dimasukkan sebagai pelanggaran; perlu diganti dengan kriteria tindakan yang objektif dan berlaku setara bagi semua siswa. Setelah migration, jalankan `php artisan db:seed --class=DaftarKodePelanggaranResmiSeeder`; seeder memeriksa benturan kode dan tidak menimpa master yang sudah memiliki catatan dengan data berbeda.
- Tindak lanjut resmi tampil pada rekap berdasarkan total poin pelanggaran sebelum pengurangan reward, untuk rentang 1–25, 26–50, 51–75, 76–99, dan tepat 100. Dokumen sumber tidak menetapkan perlakuan di atas 100, sehingga sistem menandainya sebagai belum memiliki rujukan; logika status/trigger lama tidak diubah.
- Modul Nilai Siswa tidak lagi tersedia di aplikasi. Tabel lama tidak dihapus agar data database yang sudah ada tetap aman.
- Proses asesmen publik memakai token link/QR, validasi identitas siswa, dan transaksi untuk menyimpan jawaban serta hasil.
- Rekap poin dibaca dari tabel rekap yang diperbarui oleh trigger database.
- Hak akses diperiksa oleh middleware route; menyembunyikan tautan menu bukan pengganti pemeriksaan akses.
