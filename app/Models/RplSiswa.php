<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RplSiswa extends Model {
    protected $table = 'rpl_siswa';
    protected $fillable = ['rpl_id','siswa_id'];
    public $timestamps = false;
}
