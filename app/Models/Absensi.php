<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = ['siswa_id','tanggal','status','keterangan'];
    public function siswa() { return $this->belongsTo(Siswa::class); }
}
