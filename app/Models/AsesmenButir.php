<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenButir extends Model {
    protected $table = 'asesmen_butir';
    protected $fillable = ['instrumen_id','nomor','pertanyaan','kategori','bobot'];
}
