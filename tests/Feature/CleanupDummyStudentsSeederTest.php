<?php

namespace Tests\Feature;

use Database\Seeders\CleanupDummyStudentsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CleanupDummyStudentsSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role');
        });
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nis', 20)->unique();
            $table->string('nama', 150);
        });

        foreach ([
            'absensi' => 'siswa_id',
            'ajuan_konseling' => 'siswa_id',
            'alumni' => 'siswa_id',
            'asesmen_jawaban' => 'siswa_id',
            'asesmen_jawaban_pilihan' => 'siswa_dipilih_id',
            'catatan_anekdot' => 'siswa_id',
            'home_visit' => 'siswa_id',
            'jadwal_layanan' => 'siswa_id',
            'nilai_siswa' => 'siswa_id',
            'poin_siswa' => 'siswa_id',
            'poin_siswa_rekap' => 'siswa_id',
            'rpl_siswa' => 'siswa_id',
            'surat' => 'siswa_id',
        ] as $tableName => $studentColumn) {
            Schema::create($tableName, function (Blueprint $table) use ($studentColumn) {
                $table->id();
                $table->unsignedBigInteger($studentColumn);
            });
        }
        Schema::create('bk_formulir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id')->nullable();
            $table->unsignedBigInteger('siswa_pihak_2_id')->nullable();
        });
    }

    public function test_cleanup_removes_only_known_dummy_students_and_their_related_rows(): void
    {
        $dummyId = DB::table('siswa')->insertGetId([
            'nis' => '2026001',
            'nama' => 'Andi Saputra',
        ]);
        $dummyPickedId = DB::table('siswa')->insertGetId([
            'nis' => '2026002',
            'nama' => 'Bunga Lestari',
        ]);
        DB::table('siswa')->insert([
            'nis' => 'TMP-2026-2027-7-001',
            'nama' => 'Siswa Excel',
        ]);

        DB::table('absensi')->insert(['siswa_id' => $dummyId]);
        DB::table('asesmen_jawaban_pilihan')->insert(['siswa_dipilih_id' => $dummyPickedId]);
        DB::table('bk_formulir')->insert(['siswa_pihak_2_id' => $dummyId]);

        (new CleanupDummyStudentsSeeder())->run();

        $this->assertDatabaseMissing('siswa', ['id' => $dummyId]);
        $this->assertDatabaseMissing('siswa', ['id' => $dummyPickedId]);
        $this->assertDatabaseHas('siswa', ['nis' => 'TMP-2026-2027-7-001', 'nama' => 'Siswa Excel']);
        $this->assertDatabaseCount('absensi', 0);
        $this->assertDatabaseCount('asesmen_jawaban_pilihan', 0);
        $this->assertDatabaseCount('bk_formulir', 0);
    }

    public function test_cleanup_refuses_to_delete_when_dummy_nis_has_a_different_name(): void
    {
        DB::table('siswa')->insert([
            'nis' => '2026001',
            'nama' => 'Nama bukan dummy',
        ]);

        try {
            (new CleanupDummyStudentsSeeder())->run();
            $this->fail('Cleanup harus menolak data dengan NIS yang identitasnya tidak sesuai.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('tidak cocok', $exception->getMessage());
        }

        $this->assertDatabaseHas('siswa', ['nis' => '2026001', 'nama' => 'Nama bukan dummy']);
    }
}
