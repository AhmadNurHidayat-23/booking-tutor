@extends('layouts.app')
@section('title','Temukan ritme belajar Anda')
@section('content')
<section class="hero">
<div>
<span class="eyebrow">BELAJAR PERSONAL, SESUAI KEBUTUHAN</span>
<h1>Lebih leluasa bertanya.<br/>
<em>Lebih dekat memahami.</em>
</h1>
<p>Temukan tutor sesuai mata pelajaran dan jenjang Anda. Pilih waktu yang benar-benar tersedia, lalu reservasi dengan biaya yang jelas.</p>
<a class="btn primary" href="#tutor">Temukan teman belajar ↓</a>
<div class="hero-notes">
<span>PILIH PELAJARAN</span>
<span>CEK KALENDER</span>
<span>RESERVASI SESI</span>
</div>
</div>
<figure>
<img src="/images/study.jpg" alt="Catatan dan peralatan untuk sesi belajar yang terarah">
<figcaption>WAKTU UNTUK MEMAHAMI, BUKAN SEKADAR MENGHAFAL.</figcaption>
</figure>
</section>
<section class="catalog" id="tutor">
<div class="section-heading">
<div>
<span class="eyebrow">TEMUKAN YANG SESUAI</span>
<h2>Mulai dari kebutuhan Anda.</h2>
</div>
<span class="count">{{ $tutors->total() }} tutor tersedia</span>
</div>
<form class="filters" action="/#tutor" method="get">
<label>Cari tutor<input name="q" maxlength="80" value="{{ $f['q']??'' }}" placeholder="Nama atau pendekatan belajar">
</label>
<label>Mata pelajaran<select name="subject">
<option value="">Semua pelajaran</option>
@foreach(\App\Models\Tutor::SUBJECTS as $s)<option @selected(($f['subject']??'')===$s)>{{ $s }}</option>
@endforeach
</select>
</label>
<label>Jenjang<select name="level">
<option value="">Semua jenjang</option>
@foreach(\App\Models\Tutor::LEVELS as $s)<option @selected(($f['level']??'')===$s)>{{ $s }}</option>
@endforeach
</select>
</label>
<button class="btn primary" type="submit">Cari tutor</button>
</form>
<div class="tutor-grid">
@foreach($tutors as $t)<article class="tutor-card">
<div class="tutor-intro">
@if($t->photo)<img src="{{ asset('storage/'.$t->photo) }}" alt="{{ $t->name }}">
@else
<span class="initial">{{ mb_substr($t->name,0,1) }}</span>
@endif
<div>
<span class="eyebrow">{{ $t->subject }} / {{ $t->level }}</span>
<h3>{{ $t->name }}</h3>
</div>
</div>
<p>{{ $t->headline }}</p>
<div class="tutor-meta">
<span>{{ $t->mode }} · {{ $t->city }}</span>
<strong>{{ \App\Support\Format::money($t->hourly_rate) }}<small>/ jam</small>
</strong>
</div>
<a class="btn secondary full" href="/tutor/{{ $t->id }}">Profil & ketersediaan ↗</a>
</article>
@endforeach
</div>
@if($tutors->isEmpty())<div class="empty">
<span class="empty-symbol">↗</span>
<h3>Belum ada tutor yang cocok.</h3>
<p>Coba pilihan pelajaran atau kata pencarian lain. Profil ditampilkan setelah dikelola oleh petugas.</p>
</div>
@endif{{ $tutors->links() }}</section>
<section class="how">
<div>
<span class="eyebrow">DARI PILIHAN KE PERTEMUAN</span>
<h2>Sesi yang terencana.<br/>Belajar lebih tenang.</h2>
</div>
<ol>
<li>
<strong>Kenali tutornya</strong>
<p>Baca profil, pelajaran, jenjang, serta pendekatan belajar.</p>
</li>
<li>
<strong>Pilih jam tersedia</strong>
<p>Kalender menampilkan jadwal yang belum dipesan.</p>
</li>
<li>
<strong>Konfirmasi & belajar</strong>
<p>Petugas mengonfirmasi reservasi dan membagikan petunjuk sesi secara privat.</p>
</li>
</ol>
</section>
@endsection

