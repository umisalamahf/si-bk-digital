<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JadwalLayanan extends Model {
    protected $table = 'jadwal_layanan';
    protected $fillable = ['guru_bk_id','kelas_id','siswa_id','tanggal','jam_mulai','jam_selesai','jenis_layanan','tempat','keterangan'];
    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}
