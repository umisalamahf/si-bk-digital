<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// Model ini HANYA untuk dibaca (read-only).
// Isinya diisi otomatis oleh trigger trg_after_insert/update/delete_poin_siswa,
// jangan pernah di-save() manual dari sini.
class PoinSiswaRekap extends Model
{
    protected $table = 'poin_siswa_rekap';
    protected $guarded = ['id'];
    public $timestamps = true;

    // Relasi ini yang kemarin belum ada -- PoinSiswaController pakai ->with('siswa')
    // buat ambil nama siswa sekaligus, jadi Model wajib tahu relasinya ke mana.
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
}
