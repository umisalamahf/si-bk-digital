<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// Model ini HANYA untuk dibaca. Isinya diisi otomatis oleh trigger trg_after_update_siswa_status.
class Alumni extends Model {
    protected $table = 'alumni';
    protected $guarded = ['id'];
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}
