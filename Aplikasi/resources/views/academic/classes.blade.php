@extends('layouts.app')
@section('title','Kelas & penilaian')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Perkuliahan</p><h1>Kelas & penilaian</h1><p class="muted">Kelola peserta, presensi, dan penerbitan nilai tiap kelas.</p></div>
@if(auth()->user()->role==='admin')<a class="button secondary" href="{{ route('master.kelas.index') }}">Atur penawaran kelas</a>
@endif</div>
<form class="filterbar" method="get">@include('partials.period-filter')<div class="field compact grow"><label for="q">Cari mata kuliah</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Nama mata kuliah"></div><div class="field compact"><label for="status">Status nilai</label><select id="status" name="status"><option value="">Semua</option><option value="terbit" @selected(request('status')==='terbit')>Terbit</option><option value="draf" @selected(request('status')==='draf')>Belum terbit</option></select></div><button class="button secondary">Terapkan</button></form>
<section class="panel"><div class="panel-heading"><h2>Penawaran kelas</h2><span class="muted">{{ $classes->total() }} kelas</span></div><div class="table-wrap"><table><thead><tr><th>Mata kuliah</th><th>Pengampu</th><th>Kursi dipesan</th><th>Penilaian</th><th>Tindakan</th></tr></thead><tbody>
@forelse($classes as $c)<tr><td><strong>{{ $c->matakuliah }}</strong><small>{{ $c->kode_mk }} / {{ $c->kode }} · {{ $c->sks }} SKS</small></td><td>{{ $c->dosen }}</td><td>{{ $c->reserved }} / {{ $c->kapasitas }}<small>{{ $c->kapasitas-$c->reserved }} tersedia</small></td><td><span class="status {{ $c->published_at?'positive':'neutral' }}">{{ $c->published_at?'Terbit':'Belum terbit' }}</span></td><td><div class="row-actions"><a href="{{ route('grades',$c->id) }}">Nilai</a><a href="{{ route('attendance',$c->id) }}">Presensi</a></div></td></tr>
@empty<tr><td colspan="5"><div class="empty"><h3>Belum ada kelas yang sesuai</h3><p>Pilih periode lain atau ubah pencarian.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $classes->links() }}</section>
@endsection
