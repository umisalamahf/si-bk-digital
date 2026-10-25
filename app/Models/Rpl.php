<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Rpl extends Model {
    protected $table = 'rpl';
    protected $fillable = [
        'program_bk_id', 'guru_bk_id', 'kelas_id', 'jenis_rpl', 'topik', 'tanggal',
        'kelas_semester', 'pertemuan_ke', 'waktu', 'tempat', 'media',
        'tujuan', 'materi', 'masalah', 'teknik', 'tindak_lanjut',
        'satuan_pendidikan', 'kelas_fase', 'tahun_ajaran', 'jumlah_jam_pertemuan',
        'kesiapan_peserta_didik', 'karakteristik_materi', 'dimensi_profil_lulusan',
        'capaian_layanan', 'lintas_disiplin_ilmu', 'tujuan_layanan_bk', 'topik_layanan',
        'praktik_pedagogik', 'kemitraan_lingkungan_pembelajaran', 'pemanfaatan_digital',
        'kegiatan_awal', 'kegiatan_inti', 'kegiatan_penutup',
        'asesmen_awal', 'asesmen_proses', 'asesmen_akhir',
        'nama_kepala_sekolah', 'nip_kepala_sekolah', 'nama_guru_bk', 'nip_guru_bk',
    ];
    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function peserta() { return $this->belongsToMany(Siswa::class, 'rpl_siswa', 'rpl_id', 'siswa_id'); }
    public function evaluasi() { return $this->hasMany(EvaluasiLayanan::class, 'rpl_id'); }
    public function angketKepuasan() { return $this->hasMany(AngketKepuasan::class, 'rpl_id'); }
    public function pelaksanaan() { return $this->hasOne(RplPelaksanaan::class, 'rpl_id'); }

    // Label jenis RPL yang gampang dibaca di Blade
    public function labelJenis(): string
    {
        return match($this->jenis_rpl) {
            'individual' => 'Individual',
            'kelompok' => 'Kelompok',
            'klasikal' => 'Klasikal',
            'konseling_individual' => 'Konseling Individual',
            'konseling_kelompok' => 'Konseling Kelompok',
            default => $this->jenis_rpl,
        };
    }
}
