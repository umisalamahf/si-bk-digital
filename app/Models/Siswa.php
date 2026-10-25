<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    protected $table = 'siswa'; // nama tabel eksplisit, Laravel salah nebak kalau dikira-kira (Bahasa Indonesia)
    protected $fillable = [
        'kelas_id','no_absen','nis','nisn','nama','nama_panggilan','jenis_kelamin','agama','tempat_lahir',
        'tanggal_lahir','alamat','nama_ortu','no_hp_ortu','status',
        'no_hp_siswa','nama_ayah','pekerjaan_ayah','nama_ibu','pekerjaan_ibu',
        'nama_wali','hubungan_wali','pekerjaan_wali','no_hp_wali','penghasilan_orang_tua',
        'no_hp_ayah','no_hp_ibu','hobi_minat','bakat_potensi','cita_cita','kondisi_khusus',
        'catatan_guru_bk','catatan_penting',
    ];
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function rekapPoin() { return $this->hasMany(PoinSiswaRekap::class, 'siswa_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
