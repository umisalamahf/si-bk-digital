<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JenisReward extends Model
{
    protected $table = 'jenis_reward'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = ['kode','nama_reward','poin'];
}
