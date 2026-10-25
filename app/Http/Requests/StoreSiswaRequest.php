<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool { return true; } // sudah dicek middleware 'role' di routes

    public function rules(): array
    {
        $siswaId = $this->route('siswa')?->id; // null kalau create, terisi kalau edit

        return [
            'kelas_id' => ['required', 'exists:kelas,id'],
            'no_absen' => ['nullable', 'integer', 'min:1', 'max:65535'],
            // NIS diimpor dari data sekolah (bukan auto-generate), makanya WAJIB diisi manual
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis,' . $siswaId],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:siswa,nisn,' . $siswaId],
            'nama' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:50'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'nama_ortu' => ['nullable', 'string', 'max:150'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'no_hp_siswa' => ['nullable', 'string', 'max:20'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'no_hp_ayah' => ['nullable', 'string', 'max:20'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'no_hp_ibu' => ['nullable', 'string', 'max:20'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'hubungan_wali' => ['nullable', 'string', 'max:50'],
            'no_hp_wali' => ['nullable', 'string', 'max:20'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            'penghasilan_orang_tua' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'hobi_minat' => ['nullable', 'string', 'max:1000'],
            'bakat_potensi' => ['nullable', 'string', 'max:1000'],
            'cita_cita' => ['nullable', 'string', 'max:500'],
            'kondisi_khusus' => ['nullable', 'string', 'max:10000'],
            'catatan_guru_bk' => ['nullable', 'string', 'max:10000'],
            'catatan_penting' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,tidak_aktif,alumni'],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.unique' => 'NIS ini sudah dipakai siswa lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
        ];
    }
}
