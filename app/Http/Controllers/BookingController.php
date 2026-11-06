<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Slot;
use App\Services\SchedulingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $r)
    {
        $f = $r->validate(['status' => 'nullable|in:pending,confirmed,completed,cancelled']);
        $bookings = Booking::with(['user:id,name', 'slot.tutor'])->when($r->user()->role !== 'staff', fn ($q) => $q->where('user_id', $r->user()->id))->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->latest()->paginate(15)->withQueryString();

        return view('bookings', compact('bookings', 'f'));
    }

    public function store(Request $r, Slot $slot, SchedulingService $s)
    {
        abort_unless($r->user()->role === 'student', 403);
        $d = $r->validate(['topic' => 'required|string|min:10|max:500']);
        $b = $s->book($r->user(), $slot, $d['topic']);

        return redirect('/reservasi/'.$b->id)->with('success', 'Reservasi tersimpan. Jadwal ini tidak dapat dipesan pengguna lain.');
    }

    private function authorizeBooking(Request $r, Booking $booking): void
    {
        abort_unless($r->user()->role === 'staff' || $booking->user_id === $r->user()->id, 403);
    }

    public function show(Request $r, Booking $booking)
    {
        $this->authorizeBooking($r, $booking);

        return view('booking', ['booking' => $booking->load(['slot.tutor', 'user:id,name,email', 'events' => fn ($q) => $q->oldest()])]);
    }

    public function update(Request $r, Booking $booking, SchedulingService $s)
    {
        $this->authorizeBooking($r, $booking);
        $d = $r->validate(['action' => 'required|in:confirm,cancel,complete', 'note' => 'required|string|min:5|max:300']);
        $s->change($r->user(), $booking, $d['action'], $d['note']);

        return back()->with('success', 'Perubahan reservasi tercatat.');
    }
}
