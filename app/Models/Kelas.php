<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = ['sekolah_id','guru_wali_id','nama_kelas','tingkat','tahun_ajaran'];
    public function siswa() { return $this->hasMany(Siswa::class); }
}
