<?php

namespace App\Http\Controllers;

use App\Models\InstrumenBk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use RuntimeException;
use Throwable;

class InstrumenBkController extends Controller
{
    public function index()
    {
        $instrumen = InstrumenBk::latest()->paginate(15);

        return view('instrumen.index', compact('instrumen'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['required', File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])->max(10 * 1024)],
        ]);

        $upload = $request->file('file');
        $namaFile = basename(str_replace('\\', '/', $upload->getClientOriginalName()));
        $extension = $upload->guessExtension();
        if (!$extension) {
            throw new RuntimeException('Jenis file instrumen tidak dapat ditentukan.');
        }
        $namaTersimpan = Str::uuid() . '.' . $extension;
        $path = Storage::disk('local')->putFileAs('instrumen-bk', $upload, $namaTersimpan);

        if ($path === false) {
            throw new RuntimeException('File instrumen gagal disimpan ke penyimpanan lokal.');
        }

        try {
            InstrumenBk::create([
                'judul' => $data['judul'],
                'kategori' => $data['kategori'],
                'keterangan' => $data['keterangan'] ?? null,
                'nama_file' => $namaFile,
                'path_file' => $path,
                'tipe_mime' => $upload->getMimeType() ?: 'application/octet-stream',
                'ukuran_file' => $upload->getSize(),
                'user_id' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('instrumen.index')->with('status', 'Instrumen BK berhasil diunggah.');
    }

    public function download(InstrumenBk $instrumen)
    {
        if (!Storage::disk('local')->exists($instrumen->path_file)) {
            abort(404, 'File instrumen tidak ditemukan di penyimpanan.');
        }

        return Storage::disk('local')->download($instrumen->path_file, $instrumen->nama_file);
    }

    public function destroy(InstrumenBk $instrumen)
    {
        $path = $instrumen->path_file;
        $instrumen->delete();

        if (Storage::disk('local')->exists($path) && !Storage::disk('local')->delete($path)) {
            throw new RuntimeException('Data instrumen dihapus, tetapi file fisik gagal dihapus.');
        }

        return redirect()->route('instrumen.index')->with('status', 'Instrumen BK berhasil dihapus.');
    }
}
