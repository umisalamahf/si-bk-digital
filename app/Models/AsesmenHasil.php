<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenHasil extends Model {
    protected $table = 'asesmen_hasil';
    protected $guarded = ['id']; // diisi procedure, Laravel cuma baca
    protected $casts = ['detail_skor' => 'array'];
}
