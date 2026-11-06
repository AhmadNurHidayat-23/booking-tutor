<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Booking Tutor') · Teman Belajar</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<header>
<a class="brand" href="/">teman belajar<span>SESI PERSONAL, LANGKAH TERARAH</span>
</a>
<nav>
<a href="/#tutor">Cari tutor</a>
@auth<a href="/reservasi">Reservasi saya</a>
@if(auth()->user()->role==='staff')<a href="/petugas/tutor">Kelola tutor & jadwal</a>
@endif
<form action="/keluar" method="post">
@csrf
<button class="text-btn" type="submit">Keluar ↗</button>
</form>
@else
<a class="btn secondary small" href="/masuk">Masuk / Daftar</a>
@endauth
</nav>
</header>
@if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>
@endif @if($errors->any())<div class="notice error" role="alert">
@foreach($errors->all() as $error)<p>{{ $error }}</p>
@endforeach
</div>
@endif
<main>@yield('content')</main>
<footer>
<strong>teman belajar.</strong>
<span>Ruang untuk bertanya, waktu untuk memahami.</span>
<small>Reservasi jadwal nyata · Biaya transparan · Tanpa rating fiktif</small>
</footer>
</body>
</html>


