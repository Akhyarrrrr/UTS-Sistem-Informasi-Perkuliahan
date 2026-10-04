<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Ringkasan') · Sistem Informasi Perkuliahan</title><script>document.documentElement.dataset.theme=localStorage.getItem('sip-theme')||'light';</script>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<a class="skip-link" href="#main">Lewati navigasi</a>
<div class="shell">
<aside class="sidebar" id="navigation" aria-label="Navigasi utama">
    <a class="identity" href="{{ route('dashboard') }}"><span class="identity-mark">P</span><span>Perkuliahan<small>Sistem informasi akademik</small></span></a>
    <div class="nav-section">Ruang kerja {{ auth()->user()->role }}</div>
    <nav>
        <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Ringkasan</a>
        <a class="nav-link {{ request()->routeIs('schedule')?'active':'' }}" href="{{ route('schedule') }}">Jadwal perkuliahan</a>
@if(auth()->user()->role==='mahasiswa')
            <a class="nav-link {{ request()->routeIs('krs*')?'active':'' }}" href="{{ route('krs') }}">Rencana studi</a>
            <a class="nav-link {{ request()->routeIs('khs')?'active':'' }}" href="{{ route('khs') }}">Hasil studi</a>
            <a class="nav-link {{ request()->routeIs('my-attendance')?'active':'' }}" href="{{ route('my-attendance') }}">Presensi saya</a>
@else
            <a class="nav-link {{ request()->routeIs('classes','grades*','attendance*')?'active':'' }}" href="{{ route('classes') }}">Kelas & penilaian</a>
@endif
@if(auth()->user()->role==='admin')
            <a class="nav-link {{ request()->routeIs('approvals')?'active':'' }}" href="{{ route('approvals') }}">Persetujuan KRS</a>
            <a class="nav-link {{ request()->routeIs('khs')?'active':'' }}" href="{{ route('khs') }}">Rekap hasil studi</a>
            <div class="nav-section">Data akademik</div>
@foreach(['mahasiswa'=>'Mahasiswa','dosen'=>'Dosen','matakuliah'=>'Mata kuliah','kelas'=>'Kelas','jadwal'=>'Jadwal kelas','periode'=>'Periode akademik','ruang'=>'Ruang','prodi'=>'Program studi','fakultas'=>'Fakultas','skala_nilai'=>'Skala nilai','users'=>'Pengguna'] as $key=>$label)
                <a class="nav-link {{ request()->routeIs('master.'.$key.'.*')?'active':'' }}" href="{{ route('master.'.$key.'.index') }}">{{ $label }}</a>
@endforeach
            <div class="nav-section">Pengawasan</div><a class="nav-link {{ request()->routeIs('activity')?'active':'' }}" href="{{ route('activity') }}">Riwayat perubahan</a>
@endif
    </nav>
    <div class="sidebar-note"><strong>Data simulasi</strong><p>Demonstrasi UTS Manajemen dan Pemodelan Data. Aturan di sini bukan kebijakan resmi kampus.</p></div>
</aside>
<button class="nav-backdrop" aria-label="Tutup navigasi" hidden></button>
<div class="workspace">
    <header class="topbar"><div class="topbar-left"><button class="menu-button" aria-controls="navigation" aria-expanded="false">Menu</button><span>{{ $periods->firstWhere('aktif',1)?->nama ?? 'Periode belum ditentukan' }}</span></div><div class="topbar-right"><button class="theme-button" type="button" aria-label="Ganti tema">Tema gelap</button><span class="user-name">{{ auth()->user()->name }}<small>{{ ucfirst(auth()->user()->role) }}</small></span><form method="post" action="{{ route('logout') }}">@csrf<button class="text-button">Keluar</button></form></div></header>
    <main id="main" tabindex="-1">
@if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())<div class="notice error" role="alert" tabindex="-1" id="error-summary"><strong>Periksa kembali isian berikut.</strong><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
        @yield('content')
    </main>
    <footer class="page-footer"><span>Manajemen dan Pemodelan Data · Kelas A</span><span>Akhyar · 260820701100010 · 2026</span></footer>
</div></div>
</body></html>
