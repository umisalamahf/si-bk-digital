<?php

namespace Tests\Feature;

use App\Http\Middleware\SetAuditUser;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AsesmenInstrumenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('asesmen_butir');
        Schema::dropIfExists('asesmen_instrumen');
        Schema::dropIfExists('asesmen_jenis');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('asesmen_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });
        Schema::create('asesmen_instrumen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asesmen_jenis_id');
            $table->string('nama_instrumen', 150);
            $table->string('jenjang', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
        Schema::create('asesmen_butir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instrumen_id');
            $table->integer('nomor');
            $table->text('pertanyaan');
            $table->string('kategori', 50);
            $table->integer('bobot')->default(1);
            $table->timestamps();
        });

        DB::table('asesmen_jenis')->insert([
            ['id' => 1, 'kode' => 'AKPD', 'nama' => 'AKPD'],
            ['id' => 2, 'kode' => 'SOSIOMETRI', 'nama' => 'Sosiometri'],
        ]);

        $this->withoutMiddleware(SetAuditUser::class);
        $this->actingAs(User::create([
            'name' => 'Guru BK',
            'email' => 'guru-bk-asesmen@example.test',
            'password' => Hash::make('password123'),
            'role' => 'guru_bk',
        ]));
    }

    public function test_instrument_and_questions_are_saved_together(): void
    {
        $response = $this->post(route('asesmen.instrumen.store'), [
            'asesmen_jenis_id' => 1,
            'nama_instrumen' => 'AKPD Kelas VII',
            'jenjang' => 'SMP',
            'butir' => [
                ['pertanyaan' => 'Saya ingin mengetahui cara belajar.', 'kategori' => 'belajar', 'bobot' => 2],
                ['pertanyaan' => 'Saya membutuhkan bantuan memilih kegiatan.', 'kategori' => 'karier', 'bobot' => 1],
            ],
        ]);

        $response->assertRedirect(route('asesmen.instrumen.index'));
        $this->assertDatabaseHas('asesmen_instrumen', ['nama_instrumen' => 'AKPD Kelas VII']);
        $this->assertDatabaseCount('asesmen_butir', 2);
    }

    public function test_sosiometry_instrument_can_be_configured_with_a_selection_type(): void
    {
        $response = $this->from(route('asesmen.instrumen.create'))->post(route('asesmen.instrumen.store'), [
            'asesmen_jenis_id' => 2,
            'nama_instrumen' => 'Sosiometri',
            'butir' => [
                ['pertanyaan' => 'Pilih teman.', 'kategori' => 'pilihan', 'bobot' => 1],
            ],
        ]);

        $response->assertRedirect(route('asesmen.instrumen.index'));
        $this->assertDatabaseHas('asesmen_instrumen', ['nama_instrumen' => 'Sosiometri']);
        $this->assertDatabaseHas('asesmen_butir', ['kategori' => 'pilihan']);
    }
}
