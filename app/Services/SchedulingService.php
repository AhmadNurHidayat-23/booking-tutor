<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Slot;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchedulingService
{
    public function addSlot(Tutor $t, array $data): Slot
    {
        return DB::transaction(function () use ($t, $data) {
            Tutor::lockForUpdate()->findOrFail($t->id);
            $start = Carbon::parse($data['starts_at']);
            $end = $start->copy()->addMinutes((int) $data['duration']);
            if ($t->slots()->where('starts_at', '<', $end)->where('ends_at', '>', $start)->exists()) {
                throw ValidationException::withMessages(['starts_at' => 'Jadwal bertumpang tindih dengan slot yang sudah ada.']);
            }

            return $t->slots()->create(['starts_at' => $start, 'ends_at' => $end, 'duration' => $data['duration'], 'meeting_details' => $data['meeting_details'] ?? null]);
        }, 3);
    }

    public function book(User $u, Slot $slot, string $topic): Booking
    {
        return DB::transaction(function () use ($u, $slot, $topic) {
            $t = Tutor::lockForUpdate()->findOrFail($slot->tutor_id);
            $s = Slot::lockForUpdate()->findOrFail($slot->id);
            $s->setRelation('tutor', $t);
            if (! $t->active || ! $s->active || $s->starts_at->isPast() || Booking::where('active_slot_id', $s->id)->exists()) {
                throw ValidationException::withMessages(['slot' => 'Jadwal tidak tersedia atau sudah dipesan. Pilih slot lain.']);
            }$b = Booking::create(['number' => 'BT-'.strtoupper(Str::random(8)), 'user_id' => $u->id, 'slot_id' => $s->id, 'active_slot_id' => $s->id, 'hourly_rate' => $t->hourly_rate, 'fee' => $s->fee, 'topic' => $topic]);
            $b->events()->create(['actor_id' => $u->id, 'description' => 'Reservasi dibuat; jadwal ditahan untuk pemesan ini.']);

            return $b;
        }, 3);
    }

    public function change(User $actor, Booking $booking, string $action, string $note): void
    {
        DB::transaction(function () use ($actor, $booking, $action, $note) {
            $b = Booking::lockForUpdate()->with('slot')->findOrFail($booking->id);
            $staff = $actor->role === 'staff';
            abort_unless($staff || $b->user_id === $actor->id, 403);
            if ($action === 'confirm' && $staff && $b->status === 'pending' && $b->slot->starts_at->isFuture()) {
                $b->status = 'confirmed';
                $text = 'Reservasi dikonfirmasi petugas.';
            } elseif ($action === 'complete' && $staff && $b->status === 'confirmed' && $b->slot->ends_at->isPast()) {
                $b->status = 'completed';
                $text = 'Sesi ditandai selesai oleh petugas.';
            } elseif ($action === 'cancel' && in_array($b->status, ['pending', 'confirmed']) && ($staff || $b->slot->starts_at->gt(now()->addDay()))) {
                $b->status = 'cancelled';
                $b->active_slot_id = null;
                $text = 'Reservasi dibatalkan; jadwal dapat dipesan kembali.';
            } else {
                throw ValidationException::withMessages(['booking' => 'Tindakan tidak sesuai status atau waktu sesi. Pembatalan mandiri minimal 24 jam sebelum mulai.']);
            }
            $b->save();
            $b->events()->create(['actor_id' => $actor->id, 'description' => $text.' '.$note]);
        }, 3);
    }

    public function toggle(Slot $slot): void
    {
        DB::transaction(function () use ($slot) {
            $s = Slot::lockForUpdate()->findOrFail($slot->id);
            if (Booking::where('active_slot_id', $s->id)->exists()) {
                throw ValidationException::withMessages(['slot' => 'Jadwal dengan reservasi aktif tidak dapat dinonaktifkan.']);
            }$s->update(['active' => ! $s->active]);
        }, 3);
    }
}
