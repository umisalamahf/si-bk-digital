<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RplPelaksanaan extends Model
{
    protected $table = 'rpl_pelaksanaan';

    protected $fillable = [
        'rpl_id', 'jumlah_hadir', 'hasil_pelaksanaan', 'hasil_layanan', 'tindak_lanjut', 'created_by',
    ];

    public function rpl(): BelongsTo
    {
        return $this->belongsTo(Rpl::class, 'rpl_id');
    }
}
