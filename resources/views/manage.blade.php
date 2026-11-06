@extends('layouts.app')
@section('title','Kelola tutor dan jadwal')
@section('content')
<section class="workspace">
<div class="section-heading">
<div>
<span class="eyebrow">PETUGAS / KETERSEDIAAN NYATA</span>
<h1>Waktu tertata. Sesi terjaga.</h1>
<p>Jadwal tutor tidak boleh bertumpang tindih. Rentang durasi 30, 60, 90, atau 120 menit.</p>
</div>
<a class="btn primary" href="/petugas/tutor/baru">+ Tambah tutor</a>
</div>
@forelse($tutors as $t)<section class="panel schedule-panel">
<div class="section-heading">
<div>
<span class="eyebrow">{{ $t->subject }} / {{ $t->active?'AKTIF':'NONAKTIF' }}</span>
<h2>{{ $t->name }}</h2>
<p>{{ \App\Support\Format::money($t->hourly_rate) }}/jam · {{ $t->mode }}</p>
</div>
<a class="btn secondary small" href="/petugas/tutor/{{ $t->id }}/edit">Edit profil</a>
</div>
<form class="slot-form" method="post" action="/petugas/tutor/{{ $t->id }}/jadwal">
@csrf
<label>Mulai sesi (WIB)<input name="starts_at" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}">
</label>
<label>Durasi<select name="duration">
@foreach([30,60,90,120] as $minutes)<option value="{{ $minutes }}">{{ $minutes }} menit</option>
@endforeach
</select>
</label>
<label>Petunjuk pertemuan privat<input name="meeting_details" maxlength="1000" placeholder="Tautan atau petunjuk, hanya bagi pemesan terkonfirmasi">
</label>
<button class="btn primary" type="submit">Tambah jadwal</button>
</form>
<div class="schedule-list">
@forelse($t->slots as $slot)<div>
<span>{{ $slot->starts_at->format('d/m/Y H:i') }} — {{ $slot->ends_at->format('H:i') }}<small>{{ $slot->duration }} menit · {{ $slot->active?'Ditampilkan':'Nonaktif' }}</small>
</span>
<form method="post" action="/petugas/jadwal/{{ $slot->id }}">
@csrf @method('PATCH')<button class="btn small secondary" type="submit">{{ $slot->active?'Nonaktifkan':'Aktifkan' }}</button>
</form>
</div>
@empty
<p class="hint">Belum ada jadwal mendatang. Tambahkan slot pertama.</p>
@endforelse
</div>
</section>
@empty
<div class="empty">
<h3>Siap memperkenalkan tutor pertama.</h3>
<p>Tambahkan profil dan kalender sebelum menerima reservasi.</p>
<a class="btn secondary" href="/petugas/tutor/baru">Tambah tutor</a>
</div>
@endforelse
</section>
@endsection

