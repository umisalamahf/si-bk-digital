# Instrumen Refleksi Diri BK

Migration `2026_10_25_000021_seed_self_reflection_assessment_instruments` menambahkan tiga instrumen pada kerangka asesmen yang sudah ada:

- **Eksplorasi Minat dan Kekuatan yang Dirasakan**: enam area eksplorasi RIASEC.
- **Refleksi Kecerdasan Majemuk: Preferensi Aktivitas Belajar**: delapan jenis aktivitas sebagai bahan percakapan belajar.
- **Kuesioner Refleksi Diri: Kepribadian, Kebiasaan, dan Preferensi**: lima area kebiasaan dan preferensi sehari-hari.

## Asumsi penggunaan dan interpretasi

Semua butir adalah pernyataan reflektif buatan aplikasi. Instrumen tidak diklaim sebagai alat psikometri tervalidasi, tes bakat/kemampuan objektif, pengukuran IQ, diagnosis, atau penentu tipe dan pilihan karier. Area RIASEC dan delapan preferensi aktivitas hanya menjadi kerangka untuk memulai eksplorasi; jawaban menunjukkan persepsi murid saat mengisi dan dapat berubah menurut pengalaman.

Setiap butir menggunakan empat pilihan persetujuan: SS, S, TS, dan STS. Nilai aspek adalah penjumlahan respons yang searah, dengan rentang 4–16 untuk empat butir per aspek. Skor 4–8 ditampilkan sebagai “Belum banyak terasa saat ini”, 9–12 sebagai “Mulai terasa dan dapat dieksplorasi”, dan 13–16 sebagai “Sering terasa sesuai saat ini”. Area dengan skor tertinggi ditampilkan sebagai area yang paling terasa sesuai pada saat pengisian. Tidak ada pembandingan norma, skor total untuk menentukan kategori murid, atau interpretasi bahwa skor rendah merupakan kekurangan. Murid dianjurkan mencoba kegiatan nyata dan membahas hasil bersama Guru BK.

## Akses dan pengisian ulang

Instrumen menggunakan route publik dan konfirmasi identitas yang sudah tersedia. Hasil tidak ditampilkan kepada pengisi publik; rekap tetap berada di route hasil yang dilindungi middleware autentikasi dan role `admin,guru_bk`. Sesuai batasan skema dan fungsi basis data asesmen yang sudah ada, satu murid hanya dapat mengirim satu jawaban untuk setiap instrumen. Tidak ada perubahan pada unique constraint atau data lama. Jika asesmen lanjutan dibutuhkan, Guru BK dapat menggunakan instrumen terpisah yang baru, bukan menghapus atau menimpa jawaban sebelumnya.

Migration hanya menambahkan jenis/instrumen/butir yang belum ada. Rollback menghapus hanya instrumen yang dibuat migration ini dan belum dipakai oleh link atau jawaban; jenis asesmen tidak dihapus agar tidak mengganggu data lain.
