<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JurnalLayanan extends Model {
    protected $table = 'jurnal_layanan';
    protected $fillable = ['guru_bk_id','tanggal','kegiatan','sasaran','uraian','hasil','keterangan'];
}
