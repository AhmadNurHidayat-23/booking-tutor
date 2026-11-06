@extends('layouts.app')
@section('title','Riwayat reservasi')
@section('content')
@php($labels=['pending'=>'Menunggu konfirmasi','confirmed'=>'Terkonfirmasi','completed'=>'Selesai','cancelled'=>'Dibatalkan'])
<section class="workspace">
<div class="section-heading">
<div>
<span class="eyebrow">{{ auth()->user()->role==='staff'?'PETUGAS / SEMUA RESERVASI':'RUANG PELAJAR / RIWAYAT' }}</span>
<h1>Sesi yang sudah direncanakan.</h1>
<p>Lihat jadwal, biaya, dan perkembangan konfirmasi reservasi.</p>
</div>
<a class="btn secondary" href="/#tutor">Cari tutor ↗</a>
</div>
<form method="get" action="/reservasi" class="filters">
<label>Status<select name="status">
<option value="">Semua status</option>
@foreach($labels as $key=>$label)<option value="{{ $key }}" @selected(($f['status']??'')===$key)>{{ $label }}</option>
@endforeach
</select>
</label>
<button class="btn secondary" type="submit">Terapkan</button>
</form>
<div class="booking-list">
@forelse($bookings as $b)<a href="/reservasi/{{ $b->id }}">
<div>
<span class="eyebrow">{{ $b->number }} / {{ $b->slot->tutor->subject }}</span>
<h2>{{ $b->slot->tutor->name }}</h2>
<p>{{ $b->slot->starts_at->format('d/m/Y H:i') }} WIB · {{ $b->slot->duration }} menit @if(auth()->user()->role==='staff') · {{ $b->user->name }} @endif
</p>
</div>
<span class="badge {{ $b->status }}">{{ $labels[$b->status] }}</span>
<strong>{{ \App\Support\Format::money($b->fee) }}</strong>
<span>↗</span>
</a>
@empty
<div class="empty">
<h3>Belum ada reservasi pada pilihan ini.</h3>
<p>Jadwal yang Anda pesan akan tercatat di sini.</p>
</div>
@endforelse
</div>{{ $bookings->links() }}</section>
@endsection

