<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// Pengelolaan akun login. HANYA admin (lihat routes/web.php).
// Tidak ada fitur hapus akun: akun guru BK/wali punya riwayat data (FK RESTRICT), jadi cukup diubah/ganti password.
class PenggunaController extends Controller
{
    private const PERAN = ['admin', 'guru_bk', 'wali_kelas', 'kepala_sekolah'];

    public function index()
    {
        $pengguna = User::orderBy('role')->orderBy('name')->get();
        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        return view('pengguna.form', ['pengguna' => null, 'profil' => null]);
    }

    // TRANSAKSI: membuat akun + profilnya (guru_bk / guru_wali) HARUS satu paket.
    // Kalau akun terbuat tapi profil gagal, akun itu tidak boleh tersisa (guru BK tanpa profil
    // akan error di banyak halaman karena data layanan butuh guru_bk_id).
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::PERAN)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            $profil = [
                'user_id' => $user->id,
                'nama' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ];
            if ($data['role'] === 'guru_bk') {
                DB::table('guru_bk')->insert($profil);
            } elseif ($data['role'] === 'wali_kelas') {
                DB::table('guru_wali')->insert($profil);
            }
        });

        return redirect()->route('pengguna.index')->with('status', 'Akun berhasil dibuat.');
    }

    public function edit(User $pengguna)
    {
        return view('pengguna.form', ['pengguna' => $pengguna, 'profil' => $this->profil($pengguna)]);
    }

    // Peran (role) sengaja TIDAK bisa diubah setelah akun dibuat, karena tiap peran punya data profil
    // yang berbeda. Kalau salah peran, buat akun baru dengan email lain.
    public function update(Request $request, User $pengguna)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($data, $pengguna) {
            $pengguna->name = $data['name'];
            $pengguna->email = $data['email'];
            if (!empty($data['password'])) {
                $pengguna->password = Hash::make($data['password']);
            }
            $pengguna->save();

            $tabel = $this->tabelProfil($pengguna);
            if ($tabel) {
                DB::table($tabel)->where('user_id', $pengguna->id)->update([
                    'nama' => $data['name'],
                    'nip' => $data['nip'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('pengguna.index')->with('status', 'Akun berhasil diperbarui.');
    }

    public function editProfilGuruBk(Request $request)
    {
        $profil = DB::table('guru_bk')->where('user_id', $request->user()->id)->first();
        abort_unless($profil, 404, 'Profil Guru BK tidak ditemukan.');

        return view('pengguna.profil-guru-bk', compact('profil'));
    }

    public function updateProfilGuruBk(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:30'],
        ]);

        $profil = DB::table('guru_bk')->where('user_id', $request->user()->id)->first();
        abort_unless($profil, 404, 'Profil Guru BK tidak ditemukan.');

        DB::transaction(function () use ($request, $profil, $data) {
            DB::table('guru_bk')->where('id', $profil->id)->update([
                'nama' => $data['nama'],
                'nip' => $data['nip'] ?? null,
                'updated_at' => now(),
            ]);

            $request->user()->update([
                'name' => $data['nama'],
            ]);
        });

        return redirect()->route('profil-guru-bk.edit')
            ->with('status', 'Nama dan NIP Guru BK berhasil diperbarui. Data ini digunakan pada RPL yang dibuat atau diperbarui berikutnya.');
    }

    private function tabelProfil(User $u): ?string
    {
        return match ($u->role) {
            'guru_bk' => 'guru_bk',
            'wali_kelas' => 'guru_wali',
            default => null,
        };
    }

    private function profil(User $u): ?object
    {
        $tabel = $this->tabelProfil($u);
        return $tabel ? DB::table($tabel)->where('user_id', $u->id)->first() : null;
    }
}
