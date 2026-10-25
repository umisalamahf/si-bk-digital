<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenInstrumen extends Model {
    protected $table = 'asesmen_instrumen';
    protected $fillable = ['asesmen_jenis_id','nama_instrumen','jenjang','keterangan','konfigurasi_skor'];
    protected $casts = ['konfigurasi_skor' => 'array'];
    public function jenis() { return $this->belongsTo(AsesmenJenis::class, 'asesmen_jenis_id'); }
    public function butir() { return $this->hasMany(AsesmenButir::class, 'instrumen_id')->orderBy('nomor'); }
}
