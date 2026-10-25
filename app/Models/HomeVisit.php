<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomeVisit extends Model {
    protected $table = 'home_visit';
    protected $fillable = ['siswa_id','guru_bk_id','tanggal','alamat_dikunjungi','pihak_ditemui','tujuan','hasil','tindak_lanjut'];
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}
