<?php

namespace Tests\Feature;

use Database\Seeders\ImportStudents2026_2027Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportStudents2026_2027SeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->nullable();
        });
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id');
            $table->string('nama_kelas', 50);
            $table->unsignedTinyInteger('tingkat');
            $table->string('tahun_ajaran', 12);
            $table->timestamps();
        });
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->string('nis', 20)->unique();
            $table->string('nama', 150);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
        DB::table('sekolah')->insert(['id' => 1, 'nama_sekolah' => 'SMP Negeri']);
    }

    public function test_rosters_import_into_grade_classes_and_are_idempotent(): void
    {
        $seeder = new ImportStudents2026_2027Seeder();
        $seeder->run();

        $this->assertDatabaseCount('kelas', 3);
        $this->assertDatabaseCount('siswa', 1554);
        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => 'Kelas 7',
            'tingkat' => 7,
            'tahun_ajaran' => '2026/2027',
        ]);
        $this->assertDatabaseHas('siswa', [
            'nis' => 'TMP-2026-2027-7-001',
            'nama' => 'ABUBAKAR SHIDIQ',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
        $this->assertSame(511, DB::table('siswa')->where('nis', 'like', 'TMP-%')->count());

        $seeder->run();

        $this->assertDatabaseCount('kelas', 3);
        $this->assertDatabaseCount('siswa', 1554);
    }
}
