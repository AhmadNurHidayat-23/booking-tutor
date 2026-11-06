@extends('layouts.app')
@section('title','Kelola profil tutor')
@section('content')
<section class="workspace">
<a class="back-link" href="/petugas/tutor">← Kembali ke pengelolaan</a>
<div class="section-heading">
<div>
<span class="eyebrow">PETUGAS / PROFIL TUTOR</span>
<h1>{{ $tutor?'Perbarui profil tutor.':'Perkenalkan tutor baru.' }}</h1>
<p>Gunakan informasi dan foto yang telah disetujui tutor. Jangan memasukkan klaim, ulasan, atau kualifikasi tanpa dasar.</p>
</div>
</div>
<form class="panel tutor-form" method="post" enctype="multipart/form-data" action="/petugas/tutor{{ $tutor?'/'.$tutor->id:'' }}">
@csrf
<div class="form-grid">
<label>Nama tutor<input required name="name" maxlength="80" value="{{ old('name',$tutor?->name) }}">
</label>
<label>Ringkasan pendekatan<input required name="headline" maxlength="120" value="{{ old('headline',$tutor?->headline) }}">
</label>
<label>Mata pelajaran<select name="subject" required>
@foreach(\App\Models\Tutor::SUBJECTS as $v)<option @selected(old('subject',$tutor?->subject)===$v)>{{ $v }}</option>
@endforeach
</select>
</label>
<label>Jenjang<select name="level" required>
@foreach(\App\Models\Tutor::LEVELS as $v)<option @selected(old('level',$tutor?->level)===$v)>{{ $v }}</option>
@endforeach
</select>
</label>
<label>Tarif per jam (Rp)<input type="number" name="hourly_rate" required min="10000" max="1000000" value="{{ old('hourly_rate',$tutor?->hourly_rate) }}">
</label>
<label>Metode<select name="mode" required>
@foreach(\App\Models\Tutor::MODES as $v)<option @selected(old('mode',$tutor?->mode)===$v)>{{ $v }}</option>
@endforeach
</select>
</label>
<label>Kota umum<input required name="city" maxlength="60" value="{{ old('city',$tutor?->city) }}">
</label>
<label>Foto tutor (opsional)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
</label>
</div>
<label>Profil dan pendekatan belajar<textarea name="bio" required minlength="30" maxlength="1500" rows="5">{{ old('bio',$tutor?->bio) }}</textarea>
</label>
<input type="hidden" name="active" value="0">
<label class="checkbox">
<input type="checkbox" name="active" value="1" @checked(old('active',$tutor?->active??true))>Tampilkan tutor untuk reservasi</label>
<button class="btn primary" type="submit">Simpan profil tutor</button>
</form>
</section>
@endsection

