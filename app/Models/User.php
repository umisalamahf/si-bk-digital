<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    // Helper biar di Blade/Controller gampang: $user->isGuruBk(), dst.
    // Ini bukan pengganti middleware role -- cuma biar kode lebih enak dibaca.
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isGuruBk(): bool { return $this->role === 'guru_bk'; }
    public function isWaliKelas(): bool { return $this->role === 'wali_kelas'; }
    public function isKepalaSekolah(): bool { return $this->role === 'kepala_sekolah'; }
    public function isSiswa(): bool { return $this->role === 'siswa'; }
    public function siswa(): HasOne { return $this->hasOne(Siswa::class); }
}
