@extends('layouts.app')
@section('title','Hasil studi')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Kartu hasil studi</p><h1>Hasil studi</h1><p class="muted">{{ $student->nama }} · {{ $student->npm }} · {{ $period?->nama }}</p></div><div class="heading-actions no-print"><a class="button secondary icon-action" href="{{ route('export',['kind'=>'khs','mahasiswa'=>$student->id,'periode'=>$period?->id]) }}" aria-label="Ekspor CSV" title="Ekspor CSV"><x-icon name="download"/><span class="control-label sr-only">Ekspor CSV</span></a><button type="button" class="button secondary icon-action" data-print aria-label="Cetak KHS" title="Cetak KHS"><x-icon name="print"/><span class="control-label sr-only">Cetak KHS</span></button></div></div>
<form class="filterbar no-print" method="get">@include('partials.period-filter')
@if(auth()->user()->role==='admin')<div class="field compact grow"><label for="mahasiswa">Mahasiswa</label><select id="mahasiswa" name="mahasiswa">
@foreach(\Illuminate\Support\Facades\DB::table('mahasiswa')->orderBy('nama')->get() as $m)<option value="{{ $m->id }}" @selected($student->id===$m->id)>{{ $m->nama }} · {{ $m->npm }}</option>
@endforeach</select></div>
@endif<button class="button secondary icon-action" aria-label="Lihat hasil" title="Lihat hasil"><x-icon name="chart"/><span class="control-label sr-only">Lihat hasil</span></button></form>
<section class="study-summary"><div><span class="muted">{{ $result['pending']?'IPS sementara':'Indeks prestasi semester' }}</span><strong>{{ $result['ip']!==null?number_format($result['ip'],2,',','.'):'Belum tersedia' }}</strong></div><div><span class="muted">SKS dengan nilai terbit</span><strong>{{ $result['sks'] }} SKS</strong></div><div><span class="muted">IPK nilai terbit</span><strong>{{ $cumulative['ip']!==null?number_format($cumulative['ip'],2,',','.'):'Belum tersedia' }}</strong></div></section>
@if($result['pending'])<div class="notice info"><strong>{{ $result['pending'] }} hasil mata kuliah belum terbit</strong><p>IPS sementara memakai nilai yang sudah terbit. Mata kuliah yang nilainya belum terbit belum masuk perhitungan IPS. Nilai kosong tidak dihitung sebagai nol.</p></div>
@endif
<section class="panel"><div class="panel-heading"><h2>Hasil per mata kuliah</h2><span class="muted">{{ count($result['rows']) }} pengambilan</span></div><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Mata kuliah</th><th>SKS</th><th>Nilai akhir</th><th>Huruf</th><th>Angka mutu</th><th>Status</th></tr></thead><tbody>
@forelse($result['rows'] as $row)<tr><td class="course-code">{{ $row->kode }}</td><td>{{ $row->nama }}</td><td>{{ $row->sks }}</td><td>{{ $row->hasil?number_format($row->hasil['nilai'],2,',','.'):'Belum tersedia' }}</td><td>
@if($row->hasil)<strong class="grade-letter">{{ $row->hasil['huruf'] }}</strong>
@else<span class="muted">Belum terbit</span>
@endif</td><td>{{ $row->hasil?number_format($row->hasil['angka'],2,',','.'):'Belum tersedia' }}</td><td><span class="status {{ $row->hasil?'positive':'neutral' }}">{{ $row->hasil?'Terbit':'Belum terbit' }}</span></td></tr>
@empty<tr><td colspan="7"><div class="empty"><h3>Hasil studi belum tersedia</h3><p>Hasil muncul setelah KRS disetujui dan nilai diterbitkan.</p></div></td></tr>
@endforelse</tbody></table></div></section>
<div class="footnote"><strong>Cara membaca hasil</strong><p>IPS dihitung dari jumlah (SKS × angka mutu) dibagi jumlah SKS dengan nilai terbit pada semester ini. IPK memakai seluruh semester. Jika mata kuliah diulang, sistem memakai pengambilan terbaru yang nilainya sudah terbit. Aturan ini digunakan untuk demonstrasi UTS.</p></div>
@endsection
