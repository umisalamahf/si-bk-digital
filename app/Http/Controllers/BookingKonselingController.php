<?php

namespace App\Http\Controllers;

use App\Models\AjuanKonseling;
use App\Models\SlotKonseling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingKonselingController extends Controller
{
    public function staffIndex(Request $request)
    {
        $slots = SlotKonseling::with('ajuan.siswa.kelas')
            ->where('guru_bk_user_id', $request->user()->id)
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->paginate(20);

        return view('booking.staff', compact('slots'));
    }

    public function storeSlot(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'tempat' => ['nullable', 'string', 'max:150'],
        ]);

        $overlap = SlotKonseling::where('guru_bk_user_id', $request->user()->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->whereIn('status', ['tersedia', 'dipesan'])
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Waktu slot bertumpang tindih dengan slot lain.',
            ]);
        }

        SlotKonseling::create([
            ...$data,
            'guru_bk_user_id' => $request->user()->id,
        ]);

        return redirect()->route('booking-staf.index')->with('status', 'Slot konseling berhasil dibuka.');
    }

    public function studentIndex(Request $request)
    {
        $siswa = $request->user()->siswa;
        abort_unless($siswa && $siswa->status === 'aktif', 403, 'Akun ini belum terhubung dengan siswa aktif.');

        $slots = SlotKonseling::with('guruBk')
            ->where('status', 'tersedia')
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $ajuan = AjuanKonseling::with('slot.guruBk')
            ->where('siswa_id', $siswa->id)
            ->latest()
            ->get();

        return view('booking.siswa', compact('siswa', 'slots', 'ajuan'));
    }

    public function book(Request $request)
    {
        $siswa = $request->user()->siswa;
        abort_unless($siswa && $siswa->status === 'aktif', 403, 'Akun ini tidak terhubung dengan siswa aktif.');

        $data = $request->validate([
            'slot_konseling_id' => ['required', 'integer', 'exists:slot_konseling,id'],
            'keperluan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $siswa) {
            $slot = SlotKonseling::whereKey($data['slot_konseling_id'])->lockForUpdate()->firstOrFail();
            $mulai = \Illuminate\Support\Carbon::parse($slot->tanggal->toDateString() . ' ' . $slot->jam_mulai);

            if ($slot->status !== 'tersedia' || $mulai->isPast()) {
                throw ValidationException::withMessages([
                    'slot_konseling_id' => 'Slot tersebut sudah tidak tersedia.',
                ]);
            }

            $slot->ajuan()->create([
                'siswa_id' => $siswa->id,
                'keperluan' => $data['keperluan'] ?? null,
                'status' => 'dipesan',
            ]);
            $slot->update(['status' => 'dipesan']);
        });

        return redirect()->route('booking-siswa.index')->with('status', 'Booking konseling berhasil diajukan dan slot telah dipesan.');
    }

    public function updateAjuan(Request $request, AjuanKonseling $ajuan)
    {
        $data = $request->validate([
            'status' => ['required', 'in:selesai,dibatalkan'],
        ]);

        DB::transaction(function () use ($request, $ajuan, $data) {
            $slot = SlotKonseling::whereKey($ajuan->slot_konseling_id)->lockForUpdate()->firstOrFail();
            abort_unless($slot->guru_bk_user_id === $request->user()->id, 403);

            $ajuan->refresh();
            if ($ajuan->status !== 'dipesan') {
                throw ValidationException::withMessages([
                    'status' => 'Ajuan ini sudah tidak berstatus aktif.',
                ]);
            }

            $ajuan->update(['status' => $data['status']]);
            $slot->update([
                'status' => $data['status'] === 'dibatalkan' && $slot->tanggal->isToday()
                    && $slot->jam_mulai > now()->format('H:i')
                    ? 'tersedia'
                    : ($data['status'] === 'dibatalkan' && $slot->tanggal->isFuture() ? 'tersedia' : 'ditutup'),
            ]);
        });

        return redirect()->route('booking-staf.index')->with('status', 'Status ajuan berhasil diperbarui.');
    }

    public function closeSlot(Request $request, SlotKonseling $slot)
    {
        abort_unless($slot->guru_bk_user_id === $request->user()->id, 403);
        if ($slot->status !== 'tersedia') {
            throw ValidationException::withMessages([
                'slot' => 'Hanya slot yang masih tersedia yang dapat ditutup.',
            ]);
        }

        $slot->update(['status' => 'ditutup']);

        return redirect()->route('booking-staf.index')->with('status', 'Slot konseling berhasil ditutup.');
    }
}
