<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPelanggaran extends Model
{
    protected $table = 'jenis_pelanggaran';

    protected $fillable = [
        'kode',
        'nama_pelanggaran',
        'kategori',
        'kategori_kelompok',
        'penanganan',
        'poin',
    ];

    public function poinSiswa(): HasMany
    {
        return $this->hasMany(PoinSiswa::class, 'jenis_pelanggaran_id');
    }

    public function getKodePelanggaranAttribute(): ?string
    {
        return $this->kode;
    }

    public function getBobotPointAttribute(): ?int
    {
        return $this->poin === null ? null : (int) $this->poin;
    }
}
