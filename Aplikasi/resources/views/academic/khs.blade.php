@extends('layouts.app')
@section('title','Hasil studi')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Kartu hasil studi</p><h1>Hasil studi</h1><p class="muted">{{ $student->nama }} · {{ $student->npm }} · {{ $period?->nama }}</p></div><div class="heading-actions no-print"><a class="button secondary" href="{{ route('export',['kind'=>'khs','mahasiswa'=>$student->id,'periode'=>$period?->id]) }}">Ekspor CSV</a><button type="button" class="button secondary" data-print>Cetak KHS</button></div></div>
<form class="filterbar no-print" method="get">@include('partials.period-filter')
@if(auth()->user()->role==='admin')<div class="field compact grow"><label for="mahasiswa">Mahasiswa</label><select id="mahasiswa" name="mahasiswa">
@foreach(\Illuminate\Support\Facades\DB::table('mahasiswa')->orderBy('nama')->get() as $m)<option value="{{ $m->id }}" @selected($student->id===$m->id)>{{ $m->nama }} · {{ $m->npm }}</option>
@endforeach</select></div>
@endif<button class="button secondary">Lihat hasil</button></form>
<section class="study-summary"><div><span class="muted">{{ $result['pending']?'IPS sementara':'Indeks prestasi semester' }}</span><strong>{{ $result['ip']!==null?number_format($result['ip'],2,',','.'):'Belum tersedia' }}</strong></div><div><span class="muted">SKS sudah dinilai</span><strong>{{ $result['sks'] }} SKS</strong></div><div><span class="muted">IPK nilai terbit</span><strong>{{ $cumulative['ip']!==null?number_format($cumulative['ip'],2,',','.'):'Belum tersedia' }}</strong></div></section>
@if($result['pending'])<div class="notice info"><strong>{{ $result['pending'] }} mata kuliah belum menerbitkan hasil</strong><p>IPS sementara memakai nilai yang sudah terbit. Nilai kosong tidak dihitung sebagai nol.</p></div>
@endif
<section class="panel"><div class="panel-heading"><h2>Hasil per mata kuliah</h2><span class="muted">{{ count($result['rows']) }} pengambilan</span></div><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Mata kuliah</th><th>SKS</th><th>Nilai akhir</th><th>Huruf</th><th>Angka mutu</th><th>Status</th></tr></thead><tbody>
@forelse($result['rows'] as $row)<tr><td class="course-code">{{ $row->kode }}</td><td>{{ $row->nama }}</td><td>{{ $row->sks }}</td><td>{{ $row->hasil?number_format($row->hasil['nilai'],2,',','.'):'Belum tersedia' }}</td><td>
@if($row->hasil)<strong class="grade-letter">{{ $row->hasil['huruf'] }}</strong>
@else<span class="muted">Belum dinilai</span>
@endif</td><td>{{ $row->hasil?number_format($row->hasil['angka'],2,',','.'):'Belum tersedia' }}</td><td><span class="status {{ $row->hasil?'positive':'neutral' }}">{{ $row->hasil?'Terbit':'Belum terbit' }}</span></td></tr>
@empty<tr><td colspan="7"><div class="empty"><h3>Hasil studi belum tersedia</h3><p>Hasil muncul setelah KRS disetujui dan nilai diterbitkan.</p></div></td></tr>
@endforelse</tbody></table></div></section>
<div class="footnote"><strong>Cara membaca hasil</strong><p>IPS = jumlah (SKS × angka mutu) dibagi SKS yang sudah dinilai. IPK simulasi menggunakan pengambilan terbaru yang nilainya terbit untuk mata kuliah yang diulang. Kebijakan ini adalah aturan demonstrasi.</p></div>
@endsection
