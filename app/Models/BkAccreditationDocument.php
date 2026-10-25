<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkAccreditationDocument extends Model
{
    protected $table = 'bk_accreditation_documents';

    protected $fillable = [
        'judul',
        'kategori',
        'periode',
        'status',
        'keterangan',
        'nama_file',
        'path_file',
        'tipe_mime',
        'ukuran_file',
        'user_id',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
