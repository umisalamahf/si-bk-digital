<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EvaluasiLayanan extends Model {
    protected $table = 'evaluasi_layanan';
    protected $fillable = ['rpl_id','jenis_evaluasi','tanggal','hasil','tindak_lanjut'];
    public function rpl() { return $this->belongsTo(Rpl::class, 'rpl_id'); }
}
