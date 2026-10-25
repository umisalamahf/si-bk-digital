<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenJawabanPilihan extends Model
{
    protected $table = 'asesmen_jawaban_pilihan';

    protected $fillable = ['jawaban_id', 'butir_id', 'siswa_dipilih_id', 'urutan_pilihan'];

    public function jawaban()
    {
        return $this->belongsTo(AsesmenJawaban::class, 'jawaban_id');
    }

    public function butir()
    {
        return $this->belongsTo(AsesmenButir::class, 'butir_id');
    }

    public function siswaDipilih()
    {
        return $this->belongsTo(Siswa::class, 'siswa_dipilih_id');
    }
}
