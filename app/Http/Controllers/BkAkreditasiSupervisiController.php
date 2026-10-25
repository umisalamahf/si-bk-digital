<?php

namespace App\Http\Controllers;

use App\Models\BkAccreditationDocument;
use App\Models\BkSupervisionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use RuntimeException;
use Throwable;

class BkAkreditasiSupervisiController extends Controller
{
    private const CATEGORIES = [
        'program',
        'pelaksanaan',
        'evaluasi',
        'administrasi',
        'pengembangan_profesi',
        'lainnya',
    ];

    private const DOCUMENT_STATUSES = ['draft', 'in_review', 'ready'];

    private const RECORD_STATUSES = ['planned', 'in_progress', 'follow_up', 'closed'];

    public function index(Request $request)
    {
        $filter = $request->validate([
            'periode' => ['nullable', 'string', 'max:30'],
        ]);
        $periode = $filter['periode'] ?? null;

        $documents = BkAccreditationDocument::with('uploader')
            ->when($periode, fn ($query) => $query->where('periode', $periode))
            ->latest()
            ->paginate(10, ['*'], 'documents_page')
            ->withQueryString();
        $records = BkSupervisionRecord::with('creator')
            ->when($periode, fn ($query) => $query->where('periode', $periode))
            ->orderByDesc('tanggal')
            ->paginate(10, ['*'], 'records_page')
            ->withQueryString();

        return view('bk-akreditasi.index', compact('documents', 'records', 'periode'));
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'periode' => ['required', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'])->max(10 * 1024)],
        ]);

        $upload = $request->file('file');
        $extension = $upload->guessExtension();
        if (!$extension) {
            throw new RuntimeException('Jenis file bukti tidak dapat ditentukan.');
        }

        $originalName = basename(str_replace('\\', '/', $upload->getClientOriginalName()));
        $originalName = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? '');
        if ($originalName === '') {
            $originalName = 'dokumen.' . $extension;
        }

        $path = Storage::disk('local')->putFileAs(
            'bk-accreditation-evidence',
            $upload,
            Str::uuid() . '.' . strtolower($extension)
        );

        if ($path === false) {
            throw new RuntimeException('File bukti gagal disimpan ke penyimpanan lokal.');
        }

        try {
            DB::transaction(fn () => BkAccreditationDocument::create([
                'judul' => $data['judul'],
                'kategori' => $data['kategori'],
                'periode' => trim($data['periode']),
                'status' => 'draft',
                'keterangan' => $data['keterangan'] ?? null,
                'nama_file' => $originalName,
                'path_file' => $path,
                'tipe_mime' => $upload->getMimeType() ?: 'application/octet-stream',
                'ukuran_file' => $upload->getSize(),
                'user_id' => $request->user()->id,
            ]));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('bk-akreditasi.index')->with('status', 'Dokumen bukti BK berhasil disimpan.');
    }

    public function updateDocumentStatus(Request $request, BkAccreditationDocument $document)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::DOCUMENT_STATUSES)],
        ]);
        $document->update($data);

        return back()->with('status', 'Status internal dokumen berhasil diperbarui.');
    }

    public function downloadDocument(BkAccreditationDocument $document)
    {
        if (
            !Str::startsWith($document->path_file, 'bk-accreditation-evidence/')
            || str_contains($document->path_file, '..')
            || !Storage::disk('local')->exists($document->path_file)
        ) {
            abort(404, 'File bukti tidak ditemukan di penyimpanan privat.');
        }

        return Storage::disk('local')->download($document->path_file, $document->nama_file, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function storeRecord(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:supervision,performance_audit'],
            'periode' => ['required', 'string', 'max:30'],
            'tanggal' => ['required', 'date'],
            'judul' => ['required', 'string', 'max:150'],
            'temuan' => ['required', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
            'batas_tindak_lanjut' => ['nullable', 'date', 'after_or_equal:tanggal'],
        ]);
        $data['periode'] = trim($data['periode']);
        $data['status'] = 'planned';
        $data['created_by'] = $request->user()->id;
        BkSupervisionRecord::create($data);

        return redirect()->route('bk-akreditasi.index')->with('status', 'Catatan supervisi/audit BK berhasil dibuat.');
    }

    public function updateRecord(Request $request, BkSupervisionRecord $record)
    {
        $data = $request->validate([
            'temuan' => ['required', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
            'batas_tindak_lanjut' => ['nullable', 'date', 'after_or_equal:' . $record->tanggal->toDateString()],
            'status' => ['required', 'in:' . implode(',', self::RECORD_STATUSES)],
        ]);
        $record->update($data);

        return back()->with('status', 'Temuan, tindak lanjut, dan status catatan berhasil diperbarui.');
    }
}
