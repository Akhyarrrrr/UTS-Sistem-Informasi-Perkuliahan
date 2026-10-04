@extends('layouts.app')
@section('title','Rencana studi')
@section('content')
@php($locked=$krs && in_array($krs->status,['diajukan','disetujui']))
<div class="page-heading"><div><p class="eyebrow">Kartu rencana studi</p><h1>Rencana studi</h1><p class="muted">{{ $student->nama }} · {{ $student->npm }} · {{ $period->nama }}</p></div><button type="button" class="button secondary" data-print>Cetak KRS</button></div>
<form class="filterbar no-print" method="get">@include('partials.period-filter')<button class="button secondary">Lihat periode</button></form>
<section class="study-summary"><div><span class="muted">Status KRS</span><strong>{{ ucfirst($krs?->status ?? 'Belum dibuat') }}</strong></div><div><span class="muted">SKS pilihan</span><strong><span id="selected-sks">{{ $classes->whereIn('id',$selected)->sum('sks') }}</span> / {{ $limit }}</strong></div><div><span class="muted">Pengisian KRS</span><strong>{{ \Carbon\Carbon::parse($period->krs_mulai)->format('d M') }} – {{ \Carbon\Carbon::parse($period->krs_selesai)->format('d M Y') }}</strong></div></section>
@if($krs?->catatan)<div class="notice info"><strong>Catatan admin</strong><p>{{ $krs->catatan }}</p></div>
@endif
<form method="post" action="{{ route('krs.save') }}" class="panel" data-busy>@csrf<input type="hidden" name="periode_id" value="{{ $period->id }}"><div class="panel-heading"><h2>{{ $locked?'Pilihan kelas Anda':'Pilih kelas perkuliahan' }}</h2><span class="muted">Satu kelas per mata kuliah</span></div><div class="table-wrap"><table><thead><tr><th>Pilih</th><th>Mata kuliah</th><th>Pengampu</th><th>Jadwal</th><th>SKS</th><th>Kursi</th></tr></thead><tbody>
@forelse($classes as $c)
@if(!$locked || in_array($c->id,$selected))<tr><td><input class="course-choice" aria-label="Pilih {{ $c->matakuliah }} kelas {{ $c->kode }}" type="checkbox" name="kelas[]" value="{{ $c->id }}" data-sks="{{ $c->sks }}" @checked(in_array($c->id,old('kelas',$selected))) @disabled($locked || ($c->published_at && !in_array($c->id,$selected)))></td><td><strong>{{ $c->matakuliah }}</strong><small>{{ $c->kode_mk }} / {{ $c->kode }}{{ $c->published_at?' · Nilai terbit':'' }}</small></td><td>{{ $c->dosen }}</td><td>
@foreach($c->jadwal as $j)<span class="nowrap">{{ ['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$j->hari] }}, {{ substr($j->mulai,0,5) }}–{{ substr($j->selesai,0,5) }}</span><small>{{ $j->ruang ?? $j->mode }}</small>
@endforeach</td><td>{{ $c->sks }}</td><td>{{ $c->kapasitas-$c->reserved }} tersedia<small>{{ $c->reserved }} / {{ $c->kapasitas }} dipesan</small></td></tr>
@endif
@empty<tr><td colspan="6"><div class="empty"><h3>Kelas belum ditawarkan</h3><p>Pilih periode lain atau hubungi admin akademik.</p></div></td></tr>
@endforelse</tbody></table></div>
@if(!$locked)<div class="form-footer"><p class="muted">Draf belum memesan kursi. Pemeriksaan kapasitas dilakukan saat pengajuan.</p><button class="button secondary">Simpan draf KRS</button></div>
@endif<p class="form-status" role="status"></p></form>
@if($krs?->status==='draf')<section class="panel publication no-print"><h2>Ajukan rencana studi</h2><p class="muted">Pilihan yang sudah disimpan akan diperiksa lalu memesan kursi sambil menunggu persetujuan.</p><form method="post" action="{{ route('krs.submit',$krs->id) }}" data-busy>@csrf<button class="button primary">Ajukan KRS</button><p class="form-status" role="status"></p></form></section>
@endif
@endsection
