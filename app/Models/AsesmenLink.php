<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AsesmenLink extends Model {
    protected $table = 'asesmen_link';
    protected $fillable = ['instrumen_id','kelas_id','token','kadaluarsa','aktif'];

    // Cast: kolom kadaluarsa otomatis jadi objek tanggal (Carbon), aktif jadi true/false.
    // Dulu tidak ada cast, jadi perbandingan tanggal bergantung pada tebakan format teks.
    protected $casts = [
        'kadaluarsa' => 'datetime',
        'aktif' => 'boolean',
    ];

    public function instrumen() { return $this->belongsTo(AsesmenInstrumen::class, 'instrumen_id'); }
    public function kelas() { return $this->belongsTo(Kelas::class, 'kelas_id'); }

    // Cek token masih boleh dipakai: aktif DAN belum lewat masa berlaku
    public function masihBerlaku(): bool
    {
        return $this->aktif && now()->lt($this->kadaluarsa);
    }

    // Alasan kenapa link tidak bisa dipakai (null = masih berlaku).
    // Dipakai halaman publik supaya pesannya jelas, bukan satu pesan untuk semua keadaan.
    public function alasanTidakBerlaku(): ?string
    {
        if (!$this->aktif) {
            return 'Link ini sudah dinonaktifkan oleh Guru BK.';
        }
        if (now()->gte($this->kadaluarsa)) {
            return 'Masa berlaku link ini habis pada ' . $this->kadaluarsa->format('d-m-Y H:i') . '.';
        }
        return null;
    }
}
