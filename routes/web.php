<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\CatatanAnekdotController;
use App\Http\Controllers\PoinSiswaController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\AsesmenLinkController;
use App\Http\Controllers\AsesmenPublikController;
use App\Http\Controllers\AsesmenHasilController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\HomeVisitController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProgramBkController;
use App\Http\Controllers\RplController;
use App\Http\Controllers\EvaluasiLayananController;
use App\Http\Controllers\JurnalLayananController;
use App\Http\Controllers\InstrumenBkController;
use App\Http\Controllers\AsesmenInstrumenController;
use App\Http\Controllers\BkFormulirController;
use App\Http\Controllers\AkunSiswaController;
use App\Http\Controllers\BookingKonselingController;
use App\Http\Controllers\KepuasanLayananController;
use App\Http\Controllers\RplReportController;
use App\Http\Controllers\BkAkreditasiSupervisiController;
use App\Http\Controllers\StudentBkReportController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Route PUBLIK untuk siswa isi asesmen -- TIDAK butuh login sama sekali
Route::get('/asesmen/isi/{token}', [AsesmenPublikController::class, 'isi'])->name('asesmen.publik.isi');
Route::post('/asesmen/isi/{token}/konfirmasi', [AsesmenPublikController::class, 'konfirmasi'])->name('asesmen.publik.konfirmasi');
Route::get('/asesmen/isi/{token}/form', [AsesmenPublikController::class, 'form'])->name('asesmen.publik.form');
Route::post('/asesmen/isi/{token}/simpan', [AsesmenPublikController::class, 'simpan'])->name('asesmen.publik.simpan');
Route::get('/angket-kepuasan/{token}', [KepuasanLayananController::class, 'show'])
    ->middleware('throttle:10,1')->name('kepuasan.publik.form');
Route::post('/angket-kepuasan/{token}', [KepuasanLayananController::class, 'store'])
    ->middleware('throttle:5,1')->name('kepuasan.publik.simpan');

Route::middleware(['auth', 'audit.user'])->group(function () {
    Route::get('/', function () {
        if (auth()->user()->isSiswa()) {
            return redirect()->route('booking-siswa.index');
        }

        return app(DashboardController::class)->index();
    })->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Daftar siswa: semua role boleh lihat (wali_kelas otomatis dibatasi ke kelasnya di SiswaController)
    Route::middleware('role:admin,guru_bk,wali_kelas,kepala_sekolah')->group(function () {
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/rekap/cetak', [SiswaController::class, 'cetak'])->name('siswa.cetak');
    });

    Route::middleware('role:admin,guru_bk')->group(function () {
        Route::get('/siswa/{siswa}/biodata', [SiswaController::class, 'show'])->name('siswa.show');
    });

    // Rekap poin & rekap absensi: wali_kelas SENGAJA tidak dimasukkan.
    // Spesifikasi: wali kelas hanya melihat data siswa & catatan anekdot kelasnya.
    // Daftar alumni: kepala sekolah boleh melihat (read-only); aksi kelulusan/kenaikan ada di grup admin+guru_bk di bawah
    Route::middleware('role:admin,guru_bk,kepala_sekolah')->group(function () {
        Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');
        Route::get('/alumni/rekap/cetak', [AlumniController::class, 'cetak'])->name('alumni.cetak');
        Route::get('/poin', [PoinSiswaController::class, 'index'])->name('poin.index');
        Route::get('/poin/rekap/cetak', [PoinSiswaController::class, 'cetakRekap'])->name('poin.rekap.cetak');
        Route::get('/poin/detail/cetak', [PoinSiswaController::class, 'cetakDetail'])->name('poin.detail.cetak');
        Route::get('/poin/riwayat/{siswa}', [PoinSiswaController::class, 'riwayat'])->name('poin.riwayat');
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('/absensi/rekap/cetak', [AbsensiController::class, 'cetakRekap'])->name('absensi.rekap.cetak');
    });

    // Catatan anekdot = data privat anak. Sesuai jawaban Guru BK: kepala sekolah HANYA lihat rekapan
    // (angka di dashboard), bukan isi catatan; admin tidak boleh sama sekali.
    Route::middleware('role:guru_bk,wali_kelas')->group(function () {
        Route::get('/anekdot', [CatatanAnekdotController::class, 'index'])->name('anekdot.index');
    });

    Route::middleware('role:admin,guru_bk')->group(function () {
        Route::get('/jenis-pelanggaran', [JenisPelanggaranController::class, 'index'])->name('jenis-pelanggaran.index');
        Route::get('/jenis-pelanggaran/tambah', [JenisPelanggaranController::class, 'create'])->name('jenis-pelanggaran.create');
        Route::post('/jenis-pelanggaran', [JenisPelanggaranController::class, 'store'])->name('jenis-pelanggaran.store');
        Route::get('/jenis-pelanggaran/{jenisPelanggaran}/ubah', [JenisPelanggaranController::class, 'edit'])->name('jenis-pelanggaran.edit');
        Route::put('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'update'])->name('jenis-pelanggaran.update');
        Route::delete('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'destroy'])->name('jenis-pelanggaran.destroy');

        Route::get('/siswa/tambah', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('/siswa/{siswa}/ubah', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kelas}/ubah', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');

        Route::get('/absensi', [AbsensiController::class, 'pilihKelas'])->name('absensi.index');
        Route::post('/absensi', [AbsensiController::class, 'simpanMassal'])->name('absensi.simpan');

        Route::post('/poin', [PoinSiswaController::class, 'store'])->name('poin.store');
        Route::get('/poin/tambah', [PoinSiswaController::class, 'create'])->name('poin.create');

        Route::get('/asesmen/link', [AsesmenLinkController::class, 'index'])->name('asesmen.link.index');
        Route::post('/asesmen/link', [AsesmenLinkController::class, 'store'])->name('asesmen.link.store');
        Route::get('/asesmen/link/{link}/qr', [AsesmenLinkController::class, 'qr'])->name('asesmen.link.qr');
        Route::get('/asesmen/instrumen', [AsesmenInstrumenController::class, 'index'])->name('asesmen.instrumen.index');
        Route::get('/asesmen/instrumen/tambah', [AsesmenInstrumenController::class, 'create'])->name('asesmen.instrumen.create');
        Route::post('/asesmen/instrumen', [AsesmenInstrumenController::class, 'store'])->name('asesmen.instrumen.store');
        Route::delete('/asesmen/instrumen/{instrumen}', [AsesmenInstrumenController::class, 'destroy'])->name('asesmen.instrumen.destroy');
        Route::get('/asesmen/hasil', [AsesmenHasilController::class, 'index'])->name('asesmen.hasil');
        Route::get('/asesmen/hasil/cetak', [AsesmenHasilController::class, 'cetak'])->name('asesmen.hasil.cetak');
        Route::get('/laporan/siswa-bk', [StudentBkReportController::class, 'index'])->name('laporan.siswa-bk.index');
        Route::get('/laporan/siswa-bk/{siswa}', [StudentBkReportController::class, 'show'])->name('laporan.siswa-bk.show');

        Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
        Route::get('/surat/tambah', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::get('/surat/{surat}/cetak', [SuratController::class, 'cetak'])->name('surat.cetak');

        // Fase 5A: jadwal & agenda, kelulusan & kenaikan kelas, audit log
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::post('/jadwal', [JadwalController::class, 'storeJadwal'])->name('jadwal.store');
        Route::post('/agenda', [JadwalController::class, 'storeAgenda'])->name('agenda.store');

        Route::post('/alumni/lulus', [AlumniController::class, 'lulus'])->name('alumni.lulus');
        Route::post('/alumni/naik', [AlumniController::class, 'naik'])->name('alumni.naik');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        // Fase 5B: identitas sekolah (kop surat) dan ubah/hapus poin (memicu trigger UPDATE/DELETE poin)
        Route::get('/sekolah', [SekolahController::class, 'edit'])->name('sekolah.edit');
        Route::put('/sekolah', [SekolahController::class, 'update'])->name('sekolah.update');

        Route::get('/poin/{poin}/ubah', [PoinSiswaController::class, 'edit'])->name('poin.edit');
        Route::put('/poin/{poin}', [PoinSiswaController::class, 'update'])->name('poin.update');
        Route::delete('/poin/{poin}', [PoinSiswaController::class, 'destroy'])->name('poin.destroy');

        Route::get('/instrumen-bk', [InstrumenBkController::class, 'index'])->name('instrumen.index');
        Route::post('/instrumen-bk', [InstrumenBkController::class, 'store'])->name('instrumen.store');
        Route::get('/instrumen-bk/{instrumen}/unduh', [InstrumenBkController::class, 'download'])->name('instrumen.download');
        Route::delete('/instrumen-bk/{instrumen}', [InstrumenBkController::class, 'destroy'])->name('instrumen.destroy');

        Route::get('/bk-akreditasi-supervisi', [BkAkreditasiSupervisiController::class, 'index'])->name('bk-akreditasi.index');
        Route::post('/bk-akreditasi/evidence', [BkAkreditasiSupervisiController::class, 'storeDocument'])->name('bk-akreditasi.document.store');
        Route::patch('/bk-akreditasi/evidence/{document}/status', [BkAkreditasiSupervisiController::class, 'updateDocumentStatus'])->name('bk-akreditasi.document.status');
        Route::get('/bk-akreditasi/evidence/{document}/download', [BkAkreditasiSupervisiController::class, 'downloadDocument'])->name('bk-akreditasi.document.download');
        Route::post('/bk-supervisi', [BkAkreditasiSupervisiController::class, 'storeRecord'])->name('bk-supervisi.store');
        Route::patch('/bk-supervisi/{record}', [BkAkreditasiSupervisiController::class, 'updateRecord'])->name('bk-supervisi.update');
    });

    // Pengelolaan akun login: HANYA admin (sesuai jawaban Guru BK: admin mengurus akun & data master)
    Route::middleware('role:admin')->group(function () {
        Route::get('/siswa/{siswa}/akun', [AkunSiswaController::class, 'edit'])->name('siswa.akun.edit');
        Route::post('/siswa/{siswa}/akun', [AkunSiswaController::class, 'update'])->name('siswa.akun.update');

        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/tambah', [PenggunaController::class, 'create'])->name('pengguna.create');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::get('/pengguna/{pengguna}/ubah', [PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    });

    Route::middleware('role:guru_bk')->group(function () {
        Route::get('/ajuan-konseling', [BookingKonselingController::class, 'staffIndex'])->name('booking-staf.index');
        Route::post('/ajuan-konseling/slot', [BookingKonselingController::class, 'storeSlot'])->name('booking-staf.slot.store');
        Route::patch('/ajuan-konseling/slot/{slot}/tutup', [BookingKonselingController::class, 'closeSlot'])->name('booking-staf.slot.close');
        Route::patch('/ajuan-konseling/{ajuan}', [BookingKonselingController::class, 'updateAjuan'])->name('booking-staf.ajuan.update');

        Route::get('/profil-guru-bk', [PenggunaController::class, 'editProfilGuruBk'])->name('profil-guru-bk.edit');
        Route::put('/profil-guru-bk', [PenggunaController::class, 'updateProfilGuruBk'])->name('profil-guru-bk.update');

        Route::get('/bk-formulir', [BkFormulirController::class, 'index'])->name('bk-formulir.index');
        Route::get('/bk-formulir/{jenis}/tambah', [BkFormulirController::class, 'create'])->name('bk-formulir.create');
        Route::post('/bk-formulir/{jenis}', [BkFormulirController::class, 'store'])->name('bk-formulir.store');
        Route::get('/bk-formulir/arsip/{formulir}', [BkFormulirController::class, 'show'])->name('bk-formulir.show');

        Route::get('/surat/kelakuan-baik/tambah', [SuratController::class, 'createKelakuanBaik'])->name('surat.kelakuan-baik.create');
        Route::post('/surat/kelakuan-baik', [SuratController::class, 'storeKelakuanBaik'])->name('surat.kelakuan-baik.store');
        Route::get('/anekdot/tambah', [CatatanAnekdotController::class, 'create'])->name('anekdot.create');
        Route::post('/anekdot', [CatatanAnekdotController::class, 'store'])->name('anekdot.store');

        // Fase 4: Program BK, RPL, Evaluasi, Jurnal -- khusus guru_bk
        Route::get('/program', [ProgramBkController::class, 'legacyIndex'])->name('program.index');
        Route::put('/program/{program}', [ProgramBkController::class, 'legacyUpdate'])->name('program.update');
        Route::get('/program-tahunan', [ProgramBkController::class, 'tahunan'])->name('program-tahunan.index');
        Route::put('/program-tahunan/{program}', [ProgramBkController::class, 'updateTahunan'])->name('program-tahunan.update');
        Route::get('/program-semester', [ProgramBkController::class, 'semester'])->name('program-semester.index');
        Route::put('/program-semester/{program}', [ProgramBkController::class, 'updateSemester'])->name('program-semester.update');

        Route::get('/rpl', [RplController::class, 'index'])->name('rpl.index');
        Route::get('/rpl/tambah', [RplController::class, 'create'])->name('rpl.create');
        Route::post('/rpl', [RplController::class, 'store'])->name('rpl.store');
        Route::get('/rpl/{rpl}/ubah', [RplController::class, 'edit'])->name('rpl.edit');
        Route::put('/rpl/{rpl}', [RplController::class, 'update'])->name('rpl.update');
        Route::get('/rpl/{rpl}/cetak', [RplController::class, 'cetak'])->name('rpl.cetak');
        Route::post('/rpl/{rpl}/kepuasan/link', [KepuasanLayananController::class, 'createLink'])->name('rpl.kepuasan.link');
        Route::get('/rpl/{rpl}/kepuasan', [KepuasanLayananController::class, 'results'])->name('rpl.kepuasan.hasil');
        Route::get('/laporan/konseling-individual', [RplReportController::class, 'konselingIndividual'])->name('laporan.konseling-individual');
        Route::get('/laporan/konseling-kelompok', [RplReportController::class, 'konselingKelompok'])->name('laporan.konseling-kelompok');
        Route::get('/laporan/bimbingan-kelompok', [RplReportController::class, 'bimbinganKelompok'])->name('laporan.bimbingan-kelompok');
        Route::get('/laporan/klasikal', [RplReportController::class, 'klasikal'])->name('laporan.klasikal');
        Route::post('/laporan/{jenis}/{rpl}/pelaksanaan', [RplReportController::class, 'saveExecution'])->name('laporan.pelaksanaan.simpan');
        Route::get('/rpl/{rpl}', [RplController::class, 'show'])->name('rpl.show');
        Route::post('/rpl/{rpl}/evaluasi', [EvaluasiLayananController::class, 'store'])->name('rpl.evaluasi.store');

        Route::get('/jurnal', [JurnalLayananController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal-bulanan', [JurnalLayananController::class, 'bulanan'])->name('jurnal.bulanan.index');
        Route::get('/jurnal-bulanan/cetak', [JurnalLayananController::class, 'cetakBulanan'])->name('jurnal.bulanan.cetak');

        // Home visit = catatan privat tentang keluarga siswa -> hanya guru_bk
        Route::get('/home-visit', [HomeVisitController::class, 'index'])->name('homevisit.index');
        Route::post('/home-visit', [HomeVisitController::class, 'store'])->name('homevisit.store');
        Route::get('/home-visit/rekap/cetak', [HomeVisitController::class, 'cetakRekap'])->name('homevisit.rekap.cetak');
        Route::get('/home-visit/{homeVisit}', [HomeVisitController::class, 'show'])->name('homevisit.show');
        Route::get('/home-visit/{homeVisit}/cetak', [HomeVisitController::class, 'cetak'])->name('homevisit.cetak');
        Route::post('/jurnal', [JurnalLayananController::class, 'store'])->name('jurnal.store');
        Route::get('/jurnal/cetak', [JurnalLayananController::class, 'cetak'])->name('jurnal.cetak');
    });

    Route::middleware('role:siswa')->group(function () {
        Route::get('/konseling-saya', [BookingKonselingController::class, 'studentIndex'])->name('booking-siswa.index');
        Route::post('/konseling-saya', [BookingKonselingController::class, 'book'])->name('booking-siswa.book');
    });
});
