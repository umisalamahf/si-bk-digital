<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProgramBk extends Model {
    protected $table = 'program_bk';
    protected $fillable = ['guru_bk_id','tahun_ajaran','jenis_program','capaian_layanan','alur_tujuan','action_plan'];
    public function rpl() { return $this->hasMany(Rpl::class, 'program_bk_id'); }
}
