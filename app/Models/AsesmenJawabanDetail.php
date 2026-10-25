<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenJawabanDetail extends Model {
    protected $table = 'asesmen_jawaban_detail';
    protected $fillable = ['jawaban_id','butir_id','nilai_jawaban'];
    public $timestamps = false;
}
