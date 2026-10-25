<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsesmenJawaban extends Model {
    protected $table = 'asesmen_jawaban';
    protected $fillable = ['siswa_id','instrumen_id','asesmen_link_id','respons_teks','tanggal_isi'];
    protected $casts = ['respons_teks' => 'array'];
    public $timestamps = true;
    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function instrumen() { return $this->belongsTo(AsesmenInstrumen::class, 'instrumen_id'); }
    public function hasil() { return $this->hasOne(AsesmenHasil::class, 'jawaban_id'); }
    public function pilihan() { return $this->hasMany(AsesmenJawabanPilihan::class, 'jawaban_id'); }
}
