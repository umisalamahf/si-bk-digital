<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PoinSiswa extends Model
{
    protected $table = 'poin_siswa'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = [
        'siswa_id','guru_bk_id','tipe','jenis_pelanggaran_id','jenis_reward_id',
        'tahun_ajaran','tanggal','poin','keterangan',
    ];
    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function jenisPelanggaran() { return $this->belongsTo(JenisPelanggaran::class); }
    public function jenisReward() { return $this->belongsTo(JenisReward::class); }
}
