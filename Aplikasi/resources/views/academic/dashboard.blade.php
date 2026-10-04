@extends('layouts.app')
@section('title','Ringkasan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Ruang kerja {{ ucfirst($user->role) }}</p><h1>{{ $user->role==='admin'?'Catatan akademik, tertata.':($user->role==='dosen'?'Perkuliahan yang Anda kelola.':'Rencana dan hasil studi Anda.') }}</h1><p class="muted">{{ $period?->nama }} · {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p></div><span class="data-label">Data simulasi</span></div>
@if($user->role==='admin')
<section class="overview"><div class="overview-main"><p class="eyebrow">Perlu ditinjau</p><strong class="large-number">{{ $pending }}</strong><h2>Pengajuan KRS menunggu keputusan</h2><p>Tinjau pilihan kelas, jumlah SKS, dan catatan mahasiswa sebelum menyetujui rencana studi.</p><a class="button primary" href="{{ route('approvals') }}">Tinjau pengajuan KRS</a></div><dl class="overview-stats"><div><dt>Mahasiswa</dt><dd>{{ $counts['mahasiswa'] }}</dd></div><div><dt>Dosen</dt><dd>{{ $counts['dosen'] }}</dd></div><div><dt>Mata kuliah</dt><dd>{{ $counts['matakuliah'] }}</dd></div><div><dt>Kelas periode ini</dt><dd>{{ $counts['kelas'] }}</dd></div></dl></section>
@elseif($user->role==='mahasiswa')
<section class="overview"><div class="overview-main"><p class="eyebrow">Rencana studi · {{ $student->npm }}</p><h2>{{ $krs?->status==='disetujui'?'Rencana studi telah disetujui':($krs?->status==='diajukan'?'Menunggu persetujuan admin':'Susun rencana studi periode ini') }}</h2><p>{{ $krs?->catatan ?? 'Pantau kelas yang dipilih dan hasil penilaian yang sudah diterbitkan dosen.' }}</p><a class="button primary" href="{{ route('krs') }}">Lihat rencana studi</a><a class="button secondary" href="{{ route('khs') }}">Lihat hasil studi</a></div><dl class="overview-stats"><div><dt>Kelas dipilih</dt><dd>{{ $classes->count() }}</dd></div><div><dt>SKS pilihan</dt><dd>{{ $classes->sum('sks') }}</dd></div><div><dt>{{ $ips['pending']?'IPS sementara':'IPS' }}</dt><dd>{{ $ips['ip']!==null?number_format($ips['ip'],2,',','.'):'Belum tersedia' }}</dd></div><div><dt>IPK nilai terbit</dt><dd>{{ $ipk['ip']!==null?number_format($ipk['ip'],2,',','.'):'Belum tersedia' }}</dd></div></dl></section>
@else
<section class="overview"><div class="overview-main"><p class="eyebrow">Penilaian dan presensi</p><h2>{{ $classes->whereNull('published_at')->count() }} kelas belum menerbitkan nilai</h2><p>Nilai kosong tetap menunggu penilaian. Terbitkan hasil setelah semua komponen peserta lengkap.</p><a class="button primary" href="{{ route('classes') }}">Kelola kelas saya</a></div><dl class="overview-stats"><div><dt>Kelas diampu</dt><dd>{{ $classes->count() }}</dd></div><div><dt>Nilai sudah terbit</dt><dd>{{ $classes->whereNotNull('published_at')->count() }}</dd></div></dl></section>
@endif
<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><h2>{{ $user->role==='admin'?'Kelas periode ini':'Kelas perkuliahan' }}</h2><a href="{{ $user->role==='mahasiswa'?route('krs'):route('classes') }}">Lihat semua</a></div><div class="class-list">
@forelse($classes->take(5) as $class)<div class="class-row"><div><span class="course-code">{{ $class->kode_mk }} / {{ $class->kode }}</span><h3>{{ $class->matakuliah }}</h3><p>{{ $class->dosen }} · {{ $class->sks }} SKS</p></div><span class="status {{ $class->published_at?'positive':'neutral' }}">{{ $class->published_at?'Nilai terbit':'Belum terbit' }}</span></div>
@empty<div class="empty"><h3>Belum ada kelas</h3><p>{{ $user->role==='mahasiswa'?'Pilih kelas melalui rencana studi.':'Kelas akan tampil setelah penawaran dibuat.' }}</p></div>
@endforelse</div></section><section class="panel"><div class="panel-heading"><h2>Agenda perkuliahan</h2><a href="{{ route('schedule') }}">Lihat jadwal</a></div><div class="agenda-list">
@forelse($schedules->take(4) as $s)<div class="agenda-row"><div class="agenda-time"><strong>{{ ['','Sen','Sel','Rab','Kam','Jum','Sab','Min'][$s->hari] }}</strong><span>{{ substr($s->mulai,0,5) }}</span></div><div><h3>{{ $s->matakuliah }}</h3><p>{{ $s->ruang ?? $s->mode }} · sampai {{ substr($s->selesai,0,5) }}</p></div></div>
@empty<div class="empty"><h3>Jadwal belum tersedia</h3><p>Jadwal tampil setelah kelas Anda ditetapkan.</p></div>
@endforelse</div></section></div>
<section class="panel"><div class="panel-heading"><h2>Perubahan terakhir</h2>
@if($user->role==='admin')<a href="{{ route('activity') }}">Buka riwayat</a>
@endif</div><div class="activity-list">
@forelse($recent as $item)<div><span>{{ $item->aksi }}<small>{{ $item->name ?? 'Sistem' }} · {{ $item->entitas }}</small></span><time>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m H:i') }}</time></div>
@empty<div class="empty"><p>Belum ada perubahan dari akun ini.</p></div>
@endforelse</div></section>
@endsection
