@extends('layouts.app')
@section('title','Jadwal perkuliahan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Agenda akademik</p><h1>Jadwal perkuliahan</h1><p class="muted">{{ $period?->nama }} · jadwal mingguan kelas yang dapat Anda akses.</p></div><button class="button secondary no-print" type="button" data-print>Cetak jadwal</button></div><form class="filterbar no-print" method="get">@include('partials.period-filter')<button class="button secondary">Lihat jadwal</button></form>
@forelse($schedules->groupBy('hari') as $day=>$items)<section class="panel schedule-day"><div class="panel-heading"><h2>{{ ['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$day] }}</h2><span class="muted">{{ $items->count() }} jadwal</span></div>
@foreach($items as $s)<div class="schedule-row"><strong class="schedule-time">{{ substr($s->mulai,0,5) }}<small>sampai {{ substr($s->selesai,0,5) }}</small></strong><div><h3>{{ $s->matakuliah }}</h3><p class="muted">Kelas {{ $s->kelas }} · {{ $s->ruang ?? $s->mode }}</p></div><span class="status neutral">{{ $s->mode }}</span>
@if($s->mode==='Daring' && $s->tautan)<a href="{{ $s->tautan }}" target="_blank" rel="noopener">Buka pertemuan</a>
@endif</div>
@endforeach</section>
@empty<section class="panel empty"><h2>Belum ada jadwal</h2><p>Jadwal mahasiswa ditampilkan untuk KRS yang telah disetujui.</p></section>
@endforelse
@endsection
