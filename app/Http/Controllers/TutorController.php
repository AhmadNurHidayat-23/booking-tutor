<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Tutor;
use App\Services\PhotoService;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TutorController extends Controller
{
    public function index(Request $r)
    {
        $f = $r->validate(['q' => 'nullable|string|max:80', 'subject' => ['nullable', Rule::in(Tutor::SUBJECTS)], 'level' => ['nullable', Rule::in(Tutor::LEVELS)]]);
        $tutors = Tutor::where('active', true)->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$v.'%')->orWhere('headline', 'like', '%'.$v.'%')))->when($f['subject'] ?? null, fn ($q, $v) => $q->where('subject', $v))->when($f['level'] ?? null, fn ($q, $v) => $q->where('level', $v))->orderBy('name')->paginate(9)->withQueryString();

        return view('home', compact('tutors', 'f'));
    }

    public function show(Request $r, Tutor $tutor)
    {
        abort_unless($tutor->active, 404);
        $f = $r->validate(['week' => 'nullable|date_format:Y-m-d']);
        $start = isset($f['week']) ? Carbon::parse($f['week'])->startOfWeek() : now()->startOfWeek();
        abort_if($start->lt(now()->startOfWeek()) || $start->gt(now()->addMonths(3)), 422);
        $end = $start->copy()->addWeek();
        $slots = $tutor->slots()->where('active', true)->where('starts_at', '>', now())->where('starts_at', '>=', $start)->where('starts_at', '<', $end)->whereDoesntHave('bookings', fn ($q) => $q->whereNotNull('active_slot_id'))->orderBy('starts_at')->get();

        return view('tutor', compact('tutor', 'slots', 'start', 'end'));
    }

    public function manage()
    {
        return view('manage', ['tutors' => Tutor::with(['slots' => fn ($q) => $q->where('ends_at', '>', now())->orderBy('starts_at')])->orderBy('name')->get()]);
    }

    public function edit(?Tutor $tutor = null)
    {
        return view('tutor-form', compact('tutor'));
    }

    public function save(Request $r, PhotoService $photos, ?Tutor $tutor = null)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'subject' => ['required', Rule::in(Tutor::SUBJECTS)], 'level' => ['required', Rule::in(Tutor::LEVELS)], 'headline' => 'required|string|max:120', 'bio' => 'required|string|min:30|max:1500', 'hourly_rate' => 'required|integer|min:10000|max:1000000', 'mode' => ['required', Rule::in(Tutor::MODES)], 'city' => 'required|string|max:60', 'active' => 'required|boolean', 'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=4000,max_height=4000']);
        if ($r->hasFile('photo')) {
            $d['photo'] = $photos->store($r->file('photo'));
        } else {
            unset($d['photo']);
        }if ($tutor) {
            $tutor->update($d);
        } else {
            Tutor::create($d);
        }

        return redirect('/petugas/tutor')->with('success', 'Profil tutor disimpan.');
    }

    public function addSlot(Request $r, Tutor $tutor, SchedulingService $s)
    {
        $d = $r->validate(['starts_at' => 'required|date_format:Y-m-d\TH:i|after:now|before:'.now()->addMonths(3)->format('Y-m-d'), 'duration' => 'required|integer|in:30,60,90,120', 'meeting_details' => 'nullable|string|max:1000']);
        $s->addSlot($tutor, $d);

        return back()->with('success', 'Jadwal tersedia ditambahkan.');
    }

    public function toggle(Slot $slot, SchedulingService $s)
    {
        $s->toggle($slot);

        return back()->with('success', 'Ketersediaan jadwal diperbarui.');
    }
}
