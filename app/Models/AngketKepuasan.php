<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AngketKepuasan extends Model
{
    protected $table = 'angket_kepuasan';

    protected $fillable = ['rpl_id', 'token_hash', 'created_by', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function rpl(): BelongsTo
    {
        return $this->belongsTo(Rpl::class, 'rpl_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ResponKepuasan::class, 'angket_kepuasan_id');
    }
}
