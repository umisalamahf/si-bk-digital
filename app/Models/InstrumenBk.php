<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumenBk extends Model
{
    protected $table = 'instrumen_bk';

    protected $fillable = [
        'judul',
        'kategori',
        'keterangan',
        'nama_file',
        'path_file',
        'tipe_mime',
        'ukuran_file',
        'user_id',
    ];
}
