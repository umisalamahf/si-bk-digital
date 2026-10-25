<?php

namespace Tests\Feature;

use App\Http\Middleware\SetAuditUser;
use App\Models\BkAccreditationDocument;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BkAkreditasiSupervisiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('bk_supervision_records');
        Schema::dropIfExists('bk_accreditation_documents');
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
        Schema::create('bk_accreditation_documents', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('kategori', 40);
            $table->string('periode', 30);
            $table->string('status', 20)->default('draft');
            $table->text('keterangan')->nullable();
            $table->string('nama_file', 255);
            $table->string('path_file', 500)->unique();
            $table->string('tipe_mime', 100);
            $table->unsignedBigInteger('ukuran_file');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('bk_supervision_records', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30);
            $table->string('periode', 30);
            $table->date('tanggal');
            $table->string('judul', 150);
            $table->text('temuan');
            $table->text('tindak_lanjut')->nullable();
            $table->date('batas_tindak_lanjut')->nullable();
            $table->string('status', 20)->default('planned');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Storage::fake('local');
        $this->withoutMiddleware(SetAuditUser::class);
    }

    public function test_guru_bk_can_upload_and_download_private_evidence(): void
    {
        $this->actingAs($this->user('guru_bk'));

        $this->post(route('bk-akreditasi.document.store'), [
            'judul' => 'Program Layanan',
            'kategori' => 'program',
            'periode' => '2026/2027',
            'file' => UploadedFile::fake()->createWithContent('program.pdf', "%PDF-1.4\nUji"),
        ])->assertRedirect(route('bk-akreditasi.index'));

        $document = BkAccreditationDocument::firstOrFail();
        $this->assertSame('draft', $document->status);
        Storage::disk('local')->assertExists($document->path_file);

        $this->get(route('bk-akreditasi.document.download', $document))
            ->assertOk()
            ->assertDownload('program.pdf');
    }

    public function test_other_roles_cannot_view_or_download_bk_evidence(): void
    {
        $this->actingAs($this->user('wali_kelas'));

        $this->get(route('bk-akreditasi.index'))->assertForbidden();
        $this->post(route('bk-supervisi.store'), [])->assertForbidden();
    }

    public function test_staff_can_create_and_update_supervision_follow_up(): void
    {
        $this->actingAs($this->user('admin'));

        $this->post(route('bk-supervisi.store'), [
            'jenis' => 'performance_audit',
            'periode' => '2026/2027',
            'tanggal' => '2026-10-25',
            'judul' => 'Evaluasi pelaksanaan layanan',
            'temuan' => 'Dokumentasi perlu dilengkapi.',
            'tindak_lanjut' => 'Melengkapi rekap.',
            'batas_tindak_lanjut' => '2026-11-15',
        ])->assertRedirect(route('bk-akreditasi.index'));

        $record = \App\Models\BkSupervisionRecord::firstOrFail();
        $this->assertSame('planned', $record->status);

        $this->patch(route('bk-supervisi.update', $record), [
            'temuan' => 'Dokumentasi periode sudah diperiksa.',
            'tindak_lanjut' => 'Unggah rekap yang diperbarui.',
            'batas_tindak_lanjut' => '2026-11-15',
            'status' => 'in_progress',
        ])->assertRedirect();

        $this->assertDatabaseHas('bk_supervision_records', [
            'id' => $record->id,
            'status' => 'in_progress',
            'tindak_lanjut' => 'Unggah rekap yang diperbarui.',
        ]);
    }

    public function test_upload_rejects_unapproved_file_types(): void
    {
        $this->actingAs($this->user('guru_bk'));

        $this->post(route('bk-akreditasi.document.store'), [
            'judul' => 'Berkas',
            'kategori' => 'program',
            'periode' => '2026/2027',
            'file' => UploadedFile::fake()->createWithContent('malware.exe', 'not an allowed document'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('bk_accreditation_documents', 0);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role . '@evidence.example.test',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
