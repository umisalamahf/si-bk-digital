<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponKepuasan extends Model
{
    protected $table = 'respon_kepuasan';

    protected $fillable = ['angket_kepuasan_id', 'kategori_responden', 'jawaban'];

    protected function casts(): array
    {
        return ['jawaban' => 'array'];
    }

    public function angket(): BelongsTo
    {
        return $this->belongsTo(AngketKepuasan::class, 'angket_kepuasan_id');
    }
}
