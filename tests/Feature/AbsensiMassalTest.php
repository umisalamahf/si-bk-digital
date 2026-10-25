<?php

namespace Tests\Feature;

use App\Http\Middleware\SetAuditUser;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AbsensiMassalTest extends TestCase
{
    private User $admin;
    private int $kelasId;
    private int $siswaId;
    private int $siswaKelasLainId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(SetAuditUser::class);
        $this->createSchema();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kelasId = DB::table('kelas')->insertGetId(['nama_kelas' => 'Kelas 7']);
        $kelasLainId = DB::table('kelas')->insertGetId(['nama_kelas' => 'Kelas 8']);
        $this->siswaId = DB::table('siswa')->insertGetId([
            'kelas_id' => $this->kelasId,
            'nama' => 'Siswa Kelas 7',
        ]);
        $this->siswaKelasLainId = DB::table('siswa')->insertGetId([
            'kelas_id' => $kelasLainId,
            'nama' => 'Siswa Kelas 8',
        ]);
    }

    public function test_absensi_saves_for_students_in_the_selected_class(): void
    {
        $this->actingAs($this->admin)
            ->from(route('absensi.index'))
            ->post(route('absensi.simpan'), [
                'kelas_id' => $this->kelasId,
                'tanggal' => '2026-10-25',
                'status' => [$this->siswaId => 'hadir'],
                'keterangan' => [$this->siswaId => 'Hadir tepat waktu'],
            ])
            ->assertRedirect(route('absensi.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $this->siswaId,
            'tanggal' => '2026-10-25',
            'status' => 'hadir',
            'keterangan' => 'Hadir tepat waktu',
        ]);
    }

    public function test_absensi_rejects_students_from_another_class_without_saving_anything(): void
    {
        $this->actingAs($this->admin)
            ->from(route('absensi.index'))
            ->post(route('absensi.simpan'), [
                'kelas_id' => $this->kelasId,
                'tanggal' => '2026-10-25',
                'status' => [
                    $this->siswaId => 'hadir',
                    $this->siswaKelasLainId => 'izin',
                ],
            ])
            ->assertRedirect(route('absensi.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('absensi', 0);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kelas');
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->date('tanggal');
            $table->string('status');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'tanggal']);
        });
    }
}
