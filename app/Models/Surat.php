<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Surat extends Model {
    protected $table = 'surat';
    protected $fillable = ['jenis_surat_id','siswa_id','guru_bk_id','nomor_surat','tanggal','perihal','isi','status'];
    public function jenisSurat() { return $this->belongsTo(JenisSurat::class, 'jenis_surat_id'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}
