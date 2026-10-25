<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CatatanAnekdot extends Model
{
    protected $table = 'catatan_anekdot'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = ['siswa_id','guru_bk_id','tanggal','kejadian','kategori','tindak_lanjut','notif_wali'];
    public function siswa() { return $this->belongsTo(Siswa::class); }
}
