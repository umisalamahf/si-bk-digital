<?php

namespace Tests\Feature;

use App\Http\Middleware\SetAuditUser;
use App\Models\User;
use App\Services\KepuasanScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Tests\TestCase;

class KepuasanLayananTest extends TestCase
{
    private User $counselor;
    private int $rplId;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(SetAuditUser::class);
        $this->createSchema();

        $this->counselor = User::create([
            'name' => 'Guru BK',
            'email' => 'bk@example.test',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);
        DB::table('guru_bk')->insert(['id' => 1, 'user_id' => $this->counselor->id]);
        $this->rplId = DB::table('rpl')->insertGetId([
            'guru_bk_id' => 1,
            'jenis_rpl' => 'konseling_individual',
            'topik' => 'Konseling',
            'tanggal' => '2026-10-20',
        ]);
        $this->token = Str::random(64);
        DB::table('angket_kepuasan')->insert([
            'rpl_id' => $this->rplId,
            'token_hash' => hash('sha256', $this->token),
            'expires_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_public_respondent_can_submit_complete_likert_answers(): void
    {
        $response = $this->post(route('kepuasan.publik.simpan', $this->token), [
            'kategori_responden' => 'peserta_didik',
            'jawaban' => [
                'kejelasan' => 4,
                'kesesuaian' => 3,
                'kenyamanan' => 4,
                'manfaat' => 3,
                'tindak_lanjut' => 2,
            ],
        ]);

        $response->assertOk()->assertViewIs('kepuasan.terkirim');
        $this->assertDatabaseHas('respon_kepuasan', [
            'kategori_responden' => 'peserta_didik',
            'angket_kepuasan_id' => 1,
        ]);
    }

    public function test_invalid_score_and_expired_link_are_rejected(): void
    {
        $this->from('/')
            ->post(route('kepuasan.publik.simpan', $this->token), [
                'kategori_responden' => 'orang_tua',
                'jawaban' => [
                    'kejelasan' => 5, 'kesesuaian' => 3, 'kenyamanan' => 3,
                    'manfaat' => 3, 'tindak_lanjut' => 3,
                ],
            ])
            ->assertSessionHasErrors('jawaban.kejelasan');
        $this->assertDatabaseCount('respon_kepuasan', 0);

        DB::table('angket_kepuasan')->where('token_hash', hash('sha256', $this->token))
            ->update(['expires_at' => now()->subSecond()]);
        $this->get(route('kepuasan.publik.form', $this->token))->assertGone();
    }

    public function test_private_results_and_reports_require_the_owning_counselor(): void
    {
        $this->get(route('laporan.konseling-individual'))->assertRedirect(route('login'));

        $student = User::create([
            'name' => 'Siswa',
            'email' => 'siswa@example.test',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        $this->actingAs($student)
            ->get(route('laporan.konseling-individual'))
            ->assertForbidden();

        $this->actingAs($this->counselor)
            ->get(route('laporan.konseling-individual'))
            ->assertOk()
            ->assertSee('Rekap Konseling Individual')
            ->assertSee('Konseling');
        $this->actingAs($this->counselor)
            ->get(route('rpl.kepuasan.hasil', $this->rplId))
            ->assertOk()
            ->assertSee('Hasil Angket Kepuasan');

        $otherCounselor = User::create([
            'name' => 'Guru BK lain',
            'email' => 'bk-lain@example.test',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);
        DB::table('guru_bk')->insert(['id' => 2, 'user_id' => $otherCounselor->id]);
        $this->actingAs($otherCounselor)
            ->get(route('rpl.kepuasan.hasil', $this->rplId))
            ->assertForbidden();
        $this->actingAs($otherCounselor)
            ->post(route('rpl.kepuasan.link', $this->rplId))
            ->assertForbidden();
    }

    public function test_execution_recap_saves_valid_attendance_and_rejects_overcount(): void
    {
        $siswaId = DB::table('siswa')->insertGetId(['nama' => 'Peserta']);
        DB::table('rpl_siswa')->insert(['rpl_id' => $this->rplId, 'siswa_id' => $siswaId]);
        $url = route('laporan.pelaksanaan.simpan', [
            'jenis' => 'konseling-individual',
            'rpl' => $this->rplId,
        ]);

        $this->actingAs($this->counselor)->post($url, [
            'jumlah_hadir' => 1,
            'hasil_pelaksanaan' => 'Sesi terlaksana sesuai rencana.',
            'hasil_layanan' => 'Konseli memahami langkah yang dibahas.',
        ])->assertRedirect();
        $this->assertDatabaseHas('rpl_pelaksanaan', [
            'rpl_id' => $this->rplId,
            'jumlah_hadir' => 1,
        ]);

        $this->from(route('laporan.konseling-individual'))
            ->post($url, [
                'jumlah_hadir' => 2,
                'hasil_pelaksanaan' => 'Sesi terlaksana.',
            ])
            ->assertSessionHasErrors('jumlah_hadir');
    }

    public function test_group_guidance_group_counseling_and_classical_reports_are_distinct(): void
    {
        foreach ([
            ['jenis_rpl' => 'kelompok', 'topik' => 'Bimbingan kelompok'],
            ['jenis_rpl' => 'konseling_kelompok', 'topik' => 'Konseling kelompok'],
            ['jenis_rpl' => 'klasikal', 'topik' => 'Layanan klasikal'],
        ] as $row) {
            DB::table('rpl')->insert($row + ['guru_bk_id' => 1, 'tanggal' => '2026-10-21']);
        }

        $this->actingAs($this->counselor)
            ->get(route('laporan.bimbingan-kelompok'))
            ->assertOk()->assertSee('Rekap Bimbingan Kelompok')->assertSee('Bimbingan kelompok')->assertDontSee('Konseling kelompok');
        $this->get(route('laporan.konseling-kelompok'))
            ->assertOk()->assertSee('Rekap Konseling Kelompok')->assertSee('Konseling kelompok')->assertDontSee('Bimbingan kelompok');
        $this->get(route('laporan.klasikal'))
            ->assertOk()->assertSee('Rekap Layanan Klasikal / Kelas Besar')->assertSee('Layanan klasikal');
    }

    public function test_likert_score_summary_uses_exact_labels_and_four_point_scale(): void
    {
        $scoring = new KepuasanScoringService();
        $response = (object) ['jawaban' => [
            'kejelasan' => 1,
            'kesesuaian' => 2,
            'kenyamanan' => 3,
            'manfaat' => 4,
            'tindak_lanjut' => 4,
        ]];
        $summary = $scoring->summarize(collect([$response]));

        $this->assertSame([1 => 'STS', 2 => 'TS', 3 => 'S', 4 => 'SS'], KepuasanScoringService::LABELS);
        $this->assertSame(1.0, $summary['kejelasan']['average']);
        $this->assertSame(25.0, $summary['kejelasan']['percentage']);
        $this->assertSame(100.0, $summary['tindak_lanjut']['percentage']);
    }

    private function createSchema(): void
    {
        foreach (['respon_kepuasan', 'rpl_pelaksanaan', 'angket_kepuasan', 'rpl_siswa', 'rpl', 'guru_bk', 'users', 'siswa', 'kelas'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('guru_bk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas')->nullable();
        });
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->string('nama')->nullable();
        });
        Schema::create('rpl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_bk_id');
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->string('jenis_rpl');
            $table->string('topik');
            $table->string('topik_layanan')->nullable();
            $table->date('tanggal');
            $table->string('kelas_semester')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('materi')->nullable();
            $table->text('masalah')->nullable();
            $table->text('teknik')->nullable();
            $table->text('tindak_lanjut')->nullable();
        });
        Schema::create('rpl_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rpl_id');
            $table->unsignedBigInteger('siswa_id');
        });
        Schema::create('angket_kepuasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rpl_id');
            $table->char('token_hash', 64)->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('respon_kepuasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('angket_kepuasan_id');
            $table->string('kategori_responden', 30);
            $table->json('jawaban');
            $table->timestamps();
        });
        Schema::create('rpl_pelaksanaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rpl_id')->unique();
            $table->unsignedSmallInteger('jumlah_hadir');
            $table->text('hasil_pelaksanaan');
            $table->text('hasil_layanan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }
}
