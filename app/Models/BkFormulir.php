<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkFormulir extends Model
{
    protected $table = 'bk_formulir';

    protected $fillable = [
        'jenis',
        'siswa_id',
        'siswa_pihak_2_id',
        'user_id',
        'tanggal',
        'data',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'data' => 'array',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function siswaPihakKedua()
    {
        return $this->belongsTo(Siswa::class, 'siswa_pihak_2_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
