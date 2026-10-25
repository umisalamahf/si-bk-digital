<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JenisSurat extends Model {
    protected $table = 'jenis_surat';
    protected $fillable = ['kode','nama_surat','template'];
}
