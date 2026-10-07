<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="description" content="Ruang kerja akademik untuk KRS, kelas, presensi, penilaian, dan hasil studi."><title>@yield('title', 'Ringkasan') · Sistem Informasi Perkuliahan</title><link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">@include('partials.theme-init')@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<a class="skip-link" href="#main">Lewati navigasi</a>
<div class="shell">
<aside class="sidebar" id="navigation" aria-label="Navigasi utama">
    <a class="identity" href="{{ route('home') }}"><span class="identity-mark">P</span><span>Perkuliahan<small>Sistem informasi akademik</small></span></a>
    <div class="nav-section">Ruang kerja {{ auth()->user()->role }}</div>
    <nav>
        <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><x-icon name="home"/><span>Ringkasan</span></a>
        <a class="nav-link {{ request()->routeIs('schedule')?'active':'' }}" href="{{ route('schedule') }}"><x-icon name="calendar"/><span>Jadwal perkuliahan</span></a>
@if(auth()->user()->role==='mahasiswa')
            <a class="nav-link {{ request()->routeIs('krs*')?'active':'' }}" href="{{ route('krs') }}"><x-icon name="book"/><span>Rencana studi</span></a>
            <a class="nav-link {{ request()->routeIs('khs')?'active':'' }}" href="{{ route('khs') }}"><x-icon name="chart"/><span>Hasil studi</span></a>
            <a class="nav-link {{ request()->routeIs('my-attendance')?'active':'' }}" href="{{ route('my-attendance') }}"><x-icon name="check"/><span>Presensi saya</span></a>
@else
            <a class="nav-link {{ request()->routeIs('classes','grades*','attendance*')?'active':'' }}" href="{{ route('classes') }}"><x-icon name="book"/><span>Kelas & penilaian</span></a>
@endif
@if(auth()->user()->role==='admin')
            <a class="nav-link {{ request()->routeIs('approvals')?'active':'' }}" href="{{ route('approvals') }}"><x-icon name="check"/><span>Persetujuan KRS</span></a>
            <a class="nav-link {{ request()->routeIs('khs')?'active':'' }}" href="{{ route('khs') }}"><x-icon name="chart"/><span>Rekap hasil studi</span></a>
            <div class="nav-section">Data akademik</div>
@foreach(['mahasiswa'=>'Mahasiswa','dosen'=>'Dosen','matakuliah'=>'Mata kuliah','kelas'=>'Kelas','jadwal'=>'Jadwal kelas','periode'=>'Periode akademik','ruang'=>'Ruang','prodi'=>'Program studi','fakultas'=>'Fakultas','skala_nilai'=>'Skala nilai','users'=>'Pengguna'] as $key=>$label)
                <a class="nav-link {{ request()->routeIs('master.'.$key.'.*')?'active':'' }}" href="{{ route('master.'.$key.'.index') }}"><x-icon :name="match($key){'mahasiswa'=>'student','dosen'=>'teacher','matakuliah','kelas'=>'book','jadwal','periode'=>'calendar','users'=>'users','skala_nilai'=>'chart',default=>'relations'}"/><span>{{ $label }}</span></a>
@endforeach
            <div class="nav-section">Pengawasan</div><a class="nav-link {{ request()->routeIs('activity')?'active':'' }}" href="{{ route('activity') }}"><x-icon name="reset"/><span>Riwayat perubahan</span></a>
@endif
    </nav>
    <div class="sidebar-note"><strong>Data simulasi</strong><p>Demonstrasi UTS Manajemen dan Pemodelan Data. Aturan di sini bukan kebijakan resmi kampus.</p></div>
</aside>
<button class="nav-backdrop" aria-label="Tutup navigasi" hidden></button>
<div class="workspace">
    <header class="topbar"><div class="topbar-left"><button class="menu-button icon-action" aria-controls="navigation" aria-expanded="false" aria-label="Menu" title="Menu"><x-icon name="menu"/><span class="control-label sr-only">Menu</span></button><span>{{ $periods->firstWhere('aktif',1)?->nama ?? 'Periode belum ditentukan' }}</span></div><div class="topbar-right"><button class="theme-button icon-action" type="button" aria-label="Ganti tema" title="Tema gelap"><x-icon name="moon" class="theme-moon"/><x-icon name="sun" class="theme-sun"/><span class="control-label sr-only">Tema gelap</span></button><span class="user-name">{{ auth()->user()->name }}<small>{{ ucfirst(auth()->user()->role) }}</small></span><form method="post" action="{{ route('logout') }}">@csrf<button class="text-button icon-action" aria-label="Keluar" title="Keluar"><x-icon name="logout"/><span class="control-label sr-only">Keluar</span></button></form></div></header>
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
<dialog id="delete-confirmation" class="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-message">
    <form method="dialog">
        <div class="confirm-heading"><x-icon name="trash"/><p class="eyebrow">Perubahan data</p></div>
        <h2 id="confirm-title">Hapus catatan?</h2>
        <p id="confirm-message" class="muted"></p>
        <div class="confirm-actions">
            <button class="button secondary icon-action" value="cancel" autofocus aria-label="Batal hapus" title="Batal hapus"><x-icon name="back"/><span class="sr-only">Batal hapus</span></button>
            <button class="button danger icon-action" value="delete" aria-label="Konfirmasi hapus" title="Konfirmasi hapus"><x-icon name="trash"/><span class="sr-only">Konfirmasi hapus</span></button>
        </div>
    </form>
</dialog>
</body></html>
