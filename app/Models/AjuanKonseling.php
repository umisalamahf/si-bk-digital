<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjuanKonseling extends Model
{
    protected $table = 'ajuan_konseling';

    protected $fillable = [
        'slot_konseling_id',
        'siswa_id',
        'keperluan',
        'status',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(SlotKonseling::class, 'slot_konseling_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
