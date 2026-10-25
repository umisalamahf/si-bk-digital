<?php

namespace Tests\Feature;

use App\Http\Middleware\SetAuditUser;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InstrumenBkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('instrumen_bk');
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
        Schema::create('instrumen_bk', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('kategori', 100);
            $table->text('keterangan')->nullable();
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->string('tipe_mime', 100);
            $table->unsignedBigInteger('ukuran_file');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Storage::fake('local');
        $this->withoutMiddleware(SetAuditUser::class);
        $this->actingAs(User::create([
            'name' => 'Guru BK',
            'email' => 'guru-bk-instrumen@example.test',
            'password' => Hash::make('password123'),
            'role' => 'guru_bk',
        ]));
    }

    public function test_guru_bk_can_upload_and_download_a_private_instrument(): void
    {
        $response = $this->post(route('instrumen.store'), [
            'judul' => 'Instrumen Contoh',
            'kategori' => 'Asesmen',
            'keterangan' => 'Dokumen uji',
            'file' => UploadedFile::fake()->createWithContent('instrumen.pdf', "%PDF-1.4\nContoh PDF"),
        ]);

        $response->assertRedirect(route('instrumen.index'));
        $this->assertDatabaseHas('instrumen_bk', [
            'judul' => 'Instrumen Contoh',
            'kategori' => 'Asesmen',
            'nama_file' => 'instrumen.pdf',
        ]);

        $instrumenId = \Illuminate\Support\Facades\DB::table('instrumen_bk')->value('id');
        $instrumen = \App\Models\InstrumenBk::findOrFail($instrumenId);
        Storage::disk('local')->assertExists($instrumen->path_file);

        $this->get(route('instrumen.download', $instrumen))
            ->assertOk()
            ->assertDownload('instrumen.pdf');
    }

    public function test_deleting_an_instrument_removes_its_file_and_record(): void
    {
        $path = 'instrumen-bk/dokumen.pdf';
        $this->assertTrue(Storage::disk('local')->put($path, "%PDF-1.4\nIsi uji"));
        $instrumen = \App\Models\InstrumenBk::create([
            'judul' => 'Dokumen Uji',
            'kategori' => 'Template',
            'nama_file' => 'dokumen.pdf',
            'path_file' => $path,
            'tipe_mime' => 'application/pdf',
            'ukuran_file' => 16,
            'user_id' => auth()->id(),
        ]);

        $this->delete(route('instrumen.destroy', $instrumen))
            ->assertRedirect(route('instrumen.index'));

        $this->assertDatabaseMissing('instrumen_bk', ['id' => $instrumen->id]);
        Storage::disk('local')->assertMissing($path);
    }
}
