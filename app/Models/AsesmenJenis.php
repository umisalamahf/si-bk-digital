<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenJenis extends Model {
    protected $table = 'asesmen_jenis';
    protected $fillable = ['kode','nama'];
}
