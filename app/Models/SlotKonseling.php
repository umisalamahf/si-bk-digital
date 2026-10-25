<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotKonseling extends Model
{
    protected $table = 'slot_konseling';

    protected $fillable = [
        'guru_bk_user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guruBk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_bk_user_id');
    }

    public function ajuan(): HasMany
    {
        return $this->hasMany(AjuanKonseling::class, 'slot_konseling_id');
    }
}
