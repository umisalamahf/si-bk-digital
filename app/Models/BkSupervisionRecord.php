<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkSupervisionRecord extends Model
{
    protected $table = 'bk_supervision_records';

    protected $fillable = [
        'jenis',
        'periode',
        'tanggal',
        'judul',
        'temuan',
        'tindak_lanjut',
        'batas_tindak_lanjut',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'batas_tindak_lanjut' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
