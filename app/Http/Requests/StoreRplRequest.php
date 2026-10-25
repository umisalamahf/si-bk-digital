<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

// Aturan ini MENIRU tabel "Aturan Isi RPL per Jenis" di ERD bagian 3:
// klasikal=kelas wajib+semua siswa kelas otomatis, individual/konseling_individual=tepat 1 siswa,
// kelompok/konseling_kelompok=siswa manual, konseling_*=masalah+teknik wajib.
// Trigger database (trg_before_insert_rpl) adalah jaring pengaman kedua untuk sebagian aturan ini.
class StoreRplRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $jenis = $this->input('jenis_rpl');

        return [
            'jenis_rpl' => ['required', 'in:individual,kelompok,klasikal,konseling_individual,konseling_kelompok'],
            'topik' => [$jenis === 'klasikal' ? 'nullable' : 'required', 'string', 'max:200'],
            'tanggal' => ['required', 'date'],
            'kelas_semester' => [$jenis === 'konseling_individual' ? 'required' : 'nullable', 'string', 'max:100'],
            'pertemuan_ke' => [in_array($jenis, ['konseling_individual', 'konseling_kelompok']) ? 'required' : 'nullable', 'integer', 'min:1', 'max:65535'],
            'waktu' => [in_array($jenis, ['konseling_individual', 'konseling_kelompok']) ? 'required' : 'nullable', 'string', 'max:50'],
            'tempat' => [in_array($jenis, ['konseling_individual', 'konseling_kelompok']) ? 'required' : 'nullable', 'string', 'max:200'],
            'media' => [$jenis === 'konseling_kelompok' ? 'required' : 'nullable', 'string', 'max:5000'],
            'tujuan' => ['nullable', 'string'],
            'materi' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],

            'kelas_fase' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:100'],
            'jumlah_jam_pertemuan' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:100'],
            'kesiapan_peserta_didik' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'karakteristik_materi' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'dimensi_profil_lulusan' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'capaian_layanan' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'lintas_disiplin_ilmu' => ['nullable', 'string', 'max:5000'],
            'tujuan_layanan_bk' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'topik_layanan' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:2000'],
            'praktik_pedagogik' => ['nullable', 'string', 'max:5000'],
            'kemitraan_lingkungan_pembelajaran' => ['nullable', 'string', 'max:5000'],
            'pemanfaatan_digital' => ['nullable', 'string', 'max:5000'],
            'kegiatan_awal' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:10000'],
            'kegiatan_inti' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:15000'],
            'kegiatan_penutup' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:10000'],
            'asesmen_awal' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'asesmen_proses' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],
            'asesmen_akhir' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'string', 'max:5000'],

            'kelas_id' => [$jenis === 'klasikal' ? 'required' : 'nullable', 'exists:kelas,id'],

            'masalah' => [$jenis === 'konseling_individual' ? 'required' : 'nullable', 'string'],
            'teknik' => [$jenis === 'konseling_individual' ? 'required' : 'nullable', 'string'],

            // klasikal tidak perlu siswa_ids (otomatis semua siswa kelas)
            'siswa_ids' => [in_array($jenis, ['individual', 'kelompok', 'konseling_individual', 'konseling_kelompok']) ? 'required' : 'nullable', 'array'],
            'siswa_ids.*' => ['distinct', 'exists:siswa,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $jenis = $this->input('jenis_rpl');
            $jumlah = count($this->input('siswa_ids', []));

            // individual & konseling_individual HARUS tepat 1 siswa
            if (in_array($jenis, ['individual', 'konseling_individual']) && $jumlah !== 1) {
                $validator->errors()->add('siswa_ids', 'RPL jenis ini harus memilih TEPAT 1 siswa.');
            }
            // kelompok & konseling_kelompok minimal 2 siswa (kalau cuma 1, harusnya pakai jenis individual)
            if (in_array($jenis, ['kelompok', 'konseling_kelompok']) && $jumlah < 2) {
                $validator->errors()->add('siswa_ids', 'RPL kelompok minimal 2 siswa. Kalau cuma 1 siswa, pilih jenis individual.');
            }
        });
    }
}
