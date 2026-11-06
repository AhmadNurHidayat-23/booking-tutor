@extends('layouts.app')
@section('title',$tutor->name)
@section('content')
<section class="workspace">
<a class="back-link" href="/#tutor">← Kembali ke tutor</a>
<section class="profile">
<div class="profile-portrait">
@if($tutor->photo)<img src="{{ asset('storage/'.$tutor->photo) }}" alt="{{ $tutor->name }}">
@else
<span class="initial">{{ mb_substr($tutor->name,0,1) }}</span>
@endif
</div>
<div>
<span class="eyebrow">{{ $tutor->subject }} / {{ $tutor->level }}</span>
<h1>{{ $tutor->name }}</h1>
<h2>{{ $tutor->headline }}</h2>
<p class="bio">{{ $tutor->bio }}</p>
<div class="profile-tags">
<span>{{ $tutor->mode }}</span>
<span>{{ $tutor->city }}</span>
<strong>{{ \App\Support\Format::money($tutor->hourly_rate) }} / jam</strong>
</div>
</div>
</section>
<section class="calendar">
<div class="section-heading">
<div>
<span class="eyebrow">JADWAL TERSEDIA / WIB</span>
<h2>Temukan waktu yang pas.</h2>
<p>{{ $start->format('d M') }} — {{ $end->copy()->subDay()->format('d M Y') }}. Biaya dihitung sesuai durasi slot.</p>
</div>
<div class="week-nav">
@if($start->gt(now()->startOfWeek()))<a class="btn secondary small" href="?week={{ $start->copy()->subWeek()->format('Y-m-d') }}">← Sebelumnya</a>
@endif @if($end->lt(now()->addMonths(3)))<a class="btn secondary small" href="?week={{ $end->format('Y-m-d') }}">Berikutnya →</a>
@endif
</div>
</div>
<div class="slot-grid">
@foreach($slots as $slot)<article class="slot-card">
<span class="eyebrow">{{ $slot->starts_at->translatedFormat('l, d M') }}</span>
<h3>{{ $slot->starts_at->format('H:i') }} — {{ $slot->ends_at->format('H:i') }}</h3>
<p>{{ $slot->duration }} menit · {{ $tutor->mode }}</p>
<strong>{{ \App\Support\Format::money($slot->fee) }}</strong>
@auth @if(auth()->user()->role==='student')<form method="post" action="/jadwal/{{ $slot->id }}/pesan">
@csrf
<label>Topik / kebutuhan belajar<textarea name="topic" required minlength="10" maxlength="500" rows="2" placeholder="Contoh: latihan persamaan linear">
</textarea>
</label>
<button class="btn primary full" type="submit">Reservasi jadwal ini</button>
</form>
@else
<p class="hint">Gunakan akun pelajar untuk melakukan reservasi.</p>
@endif @else
<a class="btn primary full" href="/masuk">Masuk untuk reservasi</a>
@endauth
</article>
@endforeach
</div>
@if($slots->isEmpty())<div class="empty">
<h3>Belum ada jadwal tersedia pada minggu ini.</h3>
<p>Coba minggu berikutnya. Slot yang sudah dipesan tidak ditampilkan.</p>
</div>
@endif
<p class="hint">Pembatalan mandiri harus lebih dari 24 jam sebelum sesi. Biaya yang tampil belum mencatat pembayaran; pembayaran dilakukan sesuai kesepakatan dengan pengelola.</p>
</section>
</section>
@endsection

