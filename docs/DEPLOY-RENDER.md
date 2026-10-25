# Deploy SI-BK Digital ke Render

Konfigurasi ini menjalankan Laravel dengan Docker. Render tidak menyediakan MySQL/MariaDB terkelola untuk konfigurasi ini; siapkan database MySQL/MariaDB eksternal yang dapat diakses dari Render dan mengizinkan migration membuat function, procedure, dan trigger.

## 1. Siapkan repository

Push source code ke repository GitHub/GitLab yang dapat dihubungkan ke Render. Pastikan `.env`, kredensial, dan file roster siswa tidak masuk ke repository. `.dockerignore` juga mengecualikan file `.env` dan `storage_data` dari image.

## 2. Siapkan MySQL/MariaDB

Buat database baru untuk staging, lalu catat host, port, nama database, username, dan password. Izinkan koneksi dari Render sesuai aturan jaringan provider database. Pastikan user database mempunyai izin untuk membuat tabel, index, function, procedure, dan trigger.

Jangan gunakan database produksi untuk percobaan pertama. Siapkan backup sebelum mengimpor data sekolah.

## 3. Buat Render Web Service

1. Di Render pilih **New > Blueprint** dan hubungkan repository, atau buat **New > Web Service** dengan runtime Docker.
2. Jika menggunakan Blueprint, pilih `render.yaml` pada root repository.
3. Tunggu build selesai. Konfigurasi memasang persistent disk pada `storage/app/private` agar dokumen privat tetap tersedia setelah deploy/restart. Persistent disk memerlukan plan Render berbayar dan dapat membatasi jumlah instance.
4. Di Environment tambahkan:
   - `APP_KEY`: nilai yang dihasilkan satu kali dengan `php artisan key:generate --show` pada komputer lokal. Simpan sebagai secret dan jangan membuat key baru setiap deploy.
   - `APP_URL`: URL HTTPS service Render yang tampil di dashboard.
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dan, bila port bukan 3306, `DB_PORT`: kredensial database eksternal.
5. Pastikan `APP_DEBUG=false`. Jangan menambahkan `.env` ke Git atau membagikan secret.

## 4. Jalankan migration

Sebelum migration pertama, pastikan database staging benar dan backup tersedia. Dari Shell service Render jalankan:

```sh
php artisan migrate:status
php artisan migrate --force
```

Jangan menjalankan `migrate:fresh` pada database yang berisi data. Jika pembuatan function/trigger/procedure gagal, hentikan proses dan cek versi MySQL/MariaDB serta hak akses database. Automated tests memakai SQLite, sehingga tidak membuktikan routine MySQL berhasil dibuat.

## 5. Verifikasi deployment

- Buka URL service dan pastikan health check `/up` sehat.
- Uji login, akses berdasarkan role, data siswa, input absensi/rekap, asesmen, upload/unduh dokumen, dan PDF.
- Periksa log service serta pastikan file yang diunggah masih ada setelah restart atau deploy baru.
- Untuk kenaikan kelas atau kelulusan, gunakan hanya data uji pada staging.

## Catatan data dan storage

`storage_data` tidak disertakan dalam Docker image karena dapat berisi file roster pribadi. Import file roster secara terpisah dan aman, hanya jika memang diperlukan. Jangan menjalankan seeder produksi sebelum meninjau dampaknya dan memastikan ada backup.

Persistent disk hanya menjaga file lokal aplikasi tetap ada; disk tidak menggantikan backup. Buat backup terjadwal untuk database dan file privat, lalu uji pemulihannya.
