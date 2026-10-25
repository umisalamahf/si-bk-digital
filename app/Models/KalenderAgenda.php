<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KalenderAgenda extends Model {
    protected $table = 'kalender_agenda';
    protected $fillable = ['guru_bk_id','judul','tanggal_mulai','tanggal_selesai','deskripsi'];
}
