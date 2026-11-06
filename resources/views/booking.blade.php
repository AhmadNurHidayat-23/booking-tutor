@extends('layouts.app')
@section('title','Reservasi '.$booking->number)
@section('content')
@php($labels=['pending'=>'Menunggu konfirmasi','confirmed'=>'Terkonfirmasi','completed'=>'Selesai','cancelled'=>'Dibatalkan'])
@php($staff=auth()->user()->role==='staff')
<section class="workspace">
<a class="back-link" href="/reservasi">← Kembali ke reservasi</a>
<div class="section-heading">
<div>
<span class="eyebrow">{{ $booking->number }} / {{ $booking->slot->tutor->subject }}</span>
<h1>Sesi bersama {{ $booking->slot->tutor->name }}.</h1>
<p>Untuk {{ $booking->user->name }} · {{ $booking->slot->tutor->level }}</p>
</div>
<span class="badge {{ $booking->status }}">{{ $labels[$booking->status] }}</span>
</div>
<div class="reservation-layout">
<div>
<section class="panel">
<h2>Waktu untuk belajar.</h2>
<dl class="booking-details">
<div>
<dt>Tanggal</dt>
<dd>{{ $booking->slot->starts_at->translatedFormat('l, d F Y') }}</dd>
</div>
<div>
<dt>Waktu (WIB)</dt>
<dd>{{ $booking->slot->starts_at->format('H:i') }} — {{ $booking->slot->ends_at->format('H:i') }}</dd>
</div>
<div>
<dt>Durasi</dt>
<dd>{{ $booking->slot->duration }} menit</dd>
</div>
<div>
<dt>Metode</dt>
<dd>{{ $booking->slot->tutor->mode }}</dd>
</div>
</dl>
<div class="topic">
<strong>Kebutuhan belajar</strong>
<p>{{ $booking->topic }}</p>
</div>
@if(in_array($booking->status,['confirmed','completed'])&&$booking->slot->meeting_details)<div class="meeting">
<span class="eyebrow">PETUNJUK SESI / PRIVAT</span>
<p>{{ $booking->slot->meeting_details }}</p>
</div>
@else
<p class="hint">Petunjuk sesi tersedia setelah reservasi dikonfirmasi petugas.</p>
@endif
</section>
<section class="timeline">
<h2>Aktivitas reservasi</h2>
@foreach($booking->events as $e)<article>
<p>{{ $e->description }}</p>
<small>{{ $e->created_at->format('d/m/Y H:i') }}</small>
</article>
@endforeach
</section>
</div>
<aside class="panel fee-panel">
<span class="eyebrow">BIAYA SESI TERSIMPAN</span>
<strong class="fee">{{ \App\Support\Format::money($booking->fee) }}</strong>
<p>{{ \App\Support\Format::money($booking->hourly_rate) }}/jam × {{ $booking->slot->duration }} menit.</p>
<small>Tarif disimpan saat reservasi. Halaman ini tidak mencatat pembayaran bank.</small>
@if(in_array($booking->status,['pending','confirmed']))<form action="/reservasi/{{ $booking->id }}" method="post">
@csrf @method('PATCH')<label>Catatan perubahan<textarea name="note" required minlength="5" maxlength="300" rows="3">
</textarea>
</label>
@if($staff&&$booking->status==='pending')<button class="btn primary full" name="action" value="confirm" type="submit">Konfirmasi reservasi</button>
@endif @if($staff&&$booking->status==='confirmed'&&$booking->slot->ends_at->isPast())<button class="btn primary full" name="action" value="complete" type="submit">Tandai sesi selesai</button>
@endif
<button class="btn secondary full" name="action" value="cancel" type="submit">Batalkan reservasi</button>
<small>Pembatalan mandiri harus lebih dari 24 jam sebelum sesi. Riwayat tidak dihapus.</small>
</form>
@endif
</aside>
</div>
</section>
@endsection

