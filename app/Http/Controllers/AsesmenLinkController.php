<?php
namespace App\Http\Controllers;

use App\Models\AsesmenInstrumen;
use App\Models\AsesmenLink;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AsesmenLinkController extends Controller
{
    public function index()
    {
        $instrumen = AsesmenInstrumen::with('jenis')->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $link = AsesmenLink::with(['instrumen', 'kelas'])->orderByDesc('id')->get();
        return view('asesmen.link', compact('instrumen', 'kelas', 'link'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'instrumen_id' => ['required', 'exists:asesmen_instrumen,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'masa_berlaku_hari' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        // Panggil PROCEDURE sp_generate_link_asesmen -- token dibuat di database,
        // bukan di PHP, supaya logikanya satu tempat (bisa dipanggil dari mana saja).
        $stmt = DB::select('CALL sp_generate_link_asesmen(?, ?, ?, @token)', [
            $data['instrumen_id'], $data['kelas_id'], $data['masa_berlaku_hari'],
        ]);
        $token = DB::select('SELECT @token AS token')[0]->token;

        return back()->with('status', "Link berhasil dibuat. Token: $token")->with('token_baru', $token);
    }

    // Halaman QR siap cetak/tempel di kelas: siswa scan pakai kamera HP, langsung ke halaman asesmen.
    public function qr(AsesmenLink $link)
    {
        $link->load(['instrumen', 'kelas']);

        // route() membuat alamat lengkap dari alamat yang sedang dipakai guru membuka aplikasi
        $url = route('asesmen.publik.isi', $link->token);

        // Koreksi kesalahan level 'M' supaya QR tetap terbaca walau kertasnya agak lecek/buram
        $svg = (string) QrCode::size(320)->margin(1)->errorCorrection('M')->generate($url);
        // Buang baris deklarasi XML di awal SVG supaya aman ditempel di tengah HTML
        $svg = preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg);

        // Alamat 127.0.0.1 / localhost hanya berarti "laptop ini" -- HP siswa tidak bisa membukanya
        $hanyaLokal = in_array(parse_url($url, PHP_URL_HOST), ['127.0.0.1', 'localhost'], true);

        return view('asesmen.qr', compact('link', 'url', 'svg', 'hanyaLokal'));
    }
}
