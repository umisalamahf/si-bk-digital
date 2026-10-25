<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    // Jalankan dengan: php artisan db:seed
    // Semua akun demo passwordnya "password123" -- GANTI setelah demo ke guru!
    public function run(): void
    {
        // 1. Sekolah
        $sekolahId = DB::table('sekolah')->insertGetId([
            'npsn' => '20123456',
            'nama_sekolah' => 'SMP Contoh Negeri 1',
            'alamat' => 'Jl. Pendidikan No. 1',
            'kepala_sekolah' => 'Drs. Contoh Kepsek',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 2. Akun per role (sesuai Definition of Done: harus bisa dicoba 4 role)
        $akun = [
            ['name' => 'Admin Sistem', 'email' => 'admin@sibk.test', 'role' => 'admin'],
            ['name' => 'Bu Guru BK', 'email' => 'gurubk@sibk.test', 'role' => 'guru_bk'],
            ['name' => 'Kepala Sekolah', 'email' => 'kepsek@sibk.test', 'role' => 'kepala_sekolah'],
        ];
        $userIds = [];
        foreach ($akun as $a) {
            $userIds[$a['role']] = DB::table('users')->insertGetId([
                'name' => $a['name'], 'email' => $a['email'],
                'password' => Hash::make('password123'),
                'role' => $a['role'], 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // 3. Profil guru BK (cuma SATU, sesuai konfirmasi: aplikasi ini dipakai 1 guru BK saja)
        $guruBkId = DB::table('guru_bk')->insertGetId([
            'user_id' => $userIds['guru_bk'], 'nama' => 'Bu Guru BK', 'nip' => '198001012020122001',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 4. Master jenis pelanggaran & reward (data contoh untuk lingkungan demo).
        DB::table('jenis_pelanggaran')->insert([
            ['kode' => 'P01', 'nama_pelanggaran' => 'Terlambat masuk kelas', 'kategori' => 'ringan', 'poin' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'P02', 'nama_pelanggaran' => 'Tidak mengerjakan tugas', 'kategori' => 'ringan', 'poin' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'P03', 'nama_pelanggaran' => 'Membolos', 'kategori' => 'sedang', 'poin' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'P04', 'nama_pelanggaran' => 'Berkelahi', 'kategori' => 'berat', 'poin' => 50, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('jenis_reward')->insert([
            ['kode' => 'R01', 'nama_reward' => 'Juara lomba akademik', 'poin' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'R02', 'nama_reward' => 'Aktif kegiatan OSIS/ekskul', 'poin' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Fase 3: Asesmen & Persuratan (data contoh -- GANTI setelah Guru BK kirim instrumen & template asli)
        // --- Asesmen Gaya Belajar (CONTOH, 9 butir, 3 kategori) ---
        $jenisGayaBelajarId = DB::table('asesmen_jenis')->where('kode', 'GAYA_BELAJAR')->value('id');
        if (!$jenisGayaBelajarId) {
            $jenisGayaBelajarId = DB::table('asesmen_jenis')->insertGetId([
                'kode' => 'GAYA_BELAJAR', 'nama' => 'Gaya Belajar', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $instrumenId = DB::table('asesmen_instrumen')->insertGetId([
            'asesmen_jenis_id' => $jenisGayaBelajarId, 'nama_instrumen' => 'Gaya Belajar SMP (Contoh)',
            'jenjang' => 'SMP', 'keterangan' => 'Data contoh -- ganti dengan instrumen asli dari Guru BK',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $butirContoh = [
            ['nomor'=>1,'pertanyaan'=>'Saya lebih mudah paham kalau melihat gambar/diagram','kategori'=>'visual'],
            ['nomor'=>2,'pertanyaan'=>'Saya suka membaca buku untuk belajar','kategori'=>'visual'],
            ['nomor'=>3,'pertanyaan'=>'Saya suka mencatat dengan warna-warni','kategori'=>'visual'],
            ['nomor'=>4,'pertanyaan'=>'Saya lebih mudah paham kalau dijelaskan lisan','kategori'=>'auditori'],
            ['nomor'=>5,'pertanyaan'=>'Saya suka berdiskusi untuk memahami pelajaran','kategori'=>'auditori'],
            ['nomor'=>6,'pertanyaan'=>'Saya mudah ingat sesuatu yang pernah saya dengar','kategori'=>'auditori'],
            ['nomor'=>7,'pertanyaan'=>'Saya lebih mudah paham kalau praktik langsung','kategori'=>'kinestetik'],
            ['nomor'=>8,'pertanyaan'=>'Saya tidak suka duduk diam lama saat belajar','kategori'=>'kinestetik'],
            ['nomor'=>9,'pertanyaan'=>'Saya suka belajar sambil bergerak/praktik','kategori'=>'kinestetik'],
        ];
        foreach ($butirContoh as $b) {
            DB::table('asesmen_butir')->insert([
                'instrumen_id' => $instrumenId, 'nomor' => $b['nomor'], 'pertanyaan' => $b['pertanyaan'],
                'kategori' => $b['kategori'], 'bobot' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // --- Jenis Surat ---
        $jenisSurat = [
            ['kode'=>'SPO','nama_surat'=>'Surat Pemanggilan Orang Tua'],
            ['kode'=>'PS','nama_surat'=>'Pernyataan Siswa'],
            ['kode'=>'POT','nama_surat'=>'Pernyataan Orang Tua'],
            ['kode'=>'BAK','nama_surat'=>'Berita Acara Konferensi Kasus/Konseling'],
            ['kode'=>'RUJ','nama_surat'=>'Surat Rujukan/Alih Tangan Kasus'],
            ['kode'=>'REK','nama_surat'=>'Surat Rekomendasi'],
            ['kode'=>'SKB','nama_surat'=>'Surat Keterangan Kelakuan Baik'],
        ];
        foreach ($jenisSurat as $j) {
            DB::table('jenis_surat')->updateOrInsert(
                ['kode' => $j['kode']],
                ['nama_surat' => $j['nama_surat'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
