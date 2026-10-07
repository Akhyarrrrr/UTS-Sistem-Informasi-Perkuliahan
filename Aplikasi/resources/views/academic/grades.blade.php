@extends('layouts.app')
@section('title','Penilaian '.$class->kode_mk)
@section('content')
<div class="page-heading"><div><p class="eyebrow">{{ $class->periode }} / {{ $class->kode_mk }} / {{ $class->kode }}</p><h1>{{ $class->matakuliah }}</h1><p class="muted">{{ $class->dosen }} · {{ $participants->count() }} peserta disetujui · {{ $class->sks }} SKS</p></div><div class="heading-actions"><a class="button secondary icon-action" href="{{ route('attendance',$class->id) }}" aria-label="Buka presensi" title="Buka presensi"><x-icon name="calendar"/><span class="control-label sr-only">Buka presensi</span></a><a class="button secondary icon-action" href="{{ route('export',['kind'=>'kelas','kelas'=>$class->id]) }}" aria-label="Ekspor CSV" title="Ekspor CSV"><x-icon name="download"/><span class="control-label sr-only">Ekspor CSV</span></a></div></div>
<div class="notice {{ $class->published_at?'success':'info' }}"><strong>{{ $class->published_at?'Nilai sudah diterbitkan':'Penilaian masih draf' }}</strong><p>{{ $class->published_at?'Mahasiswa dapat melihat hasil pada KHS. Untuk memperbaiki nilai, buka koreksi dengan alasan.':'Isi nilai 0–100. Kolom kosong berarti belum dinilai. Semua nilai wajib lengkap sebelum diterbitkan.' }}</p></div>
@if(auth()->user()->role==='admin' && !$class->first_published_at)<details class="panel settings"><summary>Atur bobot penilaian</summary><form method="post" action="{{ route('weights',$class->id) }}" data-busy>@csrf<div class="form-grid">
@foreach($components as $component)<div class="field"><label for="weight-{{ $component->id }}">{{ $component->nama }} (%)</label><input id="weight-{{ $component->id }}" type="number" step="0.01" min="0.01" max="100" name="bobot[{{ $component->id }}]" value="{{ $component->bobot }}" required></div>
@endforeach</div><div class="form-footer"><p class="muted">Jumlah bobot harus 100%.</p><button class="button secondary icon-action" aria-label="Simpan bobot" title="Simpan bobot"><x-icon name="save"/><span class="control-label sr-only">Simpan bobot</span></button></div><p class="form-status" role="status"></p></form></details>
@endif
<form method="post" action="{{ route('grades.save',$class->id) }}" class="panel" data-busy>@csrf<div class="panel-heading"><h2>Nilai peserta</h2><span class="muted">{{ $components->sum('bobot') }}% total bobot</span></div><div class="table-wrap"><table class="grade-table"><thead><tr><th>Mahasiswa</th>
@foreach($components as $c)<th>{{ $c->nama }}<small>{{ (float)$c->bobot }}%</small></th>
@endforeach<th>Nilai akhir</th><th>Huruf</th></tr></thead><tbody>
@forelse($participants as $p)<tr><td><strong>{{ $p->nama }}</strong><small>{{ $p->npm }}</small></td>
@foreach($components as $c)
@php($value=$scores->get($p->id,collect())->firstWhere('komponen_nilai_id',$c->id)?->nilai)<td><input aria-label="{{ $c->nama }} {{ $p->nama }}" type="number" step="0.01" min="0" max="100" name="nilai[{{ $p->id }}][{{ $c->id }}]" value="{{ old('nilai.'.$p->id.'.'.$c->id,$value) }}" @disabled($class->published_at)></td>
@endforeach<td>{{ $p->hasil?number_format($p->hasil['nilai'],2,',','.'):'Belum lengkap' }}</td><td>
@if($p->hasil)<strong class="grade-letter">{{ $p->hasil['huruf'] }}</strong>
@else<span class="muted">Belum dinilai</span>
@endif</td></tr>
@empty<tr><td colspan="{{ $components->count()+3 }}"><div class="empty"><h3>Belum ada peserta yang disetujui</h3><p>Nilai dapat diisi setelah KRS peserta disetujui.</p></div></td></tr>
@endforelse</tbody></table></div>
@if(!$class->published_at && $participants->count())<div class="form-footer"><p class="muted">Simpan draf terlebih dahulu sebelum menerbitkan.</p><button class="button primary icon-action" aria-label="Simpan nilai" title="Simpan nilai"><x-icon name="save"/><span class="control-label sr-only">Simpan nilai</span></button></div>
@endif<p class="form-status" role="status"></p></form>
@if($participants->count())<section class="panel publication"><h2>{{ $class->published_at?'Koreksi nilai terbit':'Terbitkan ke KHS' }}</h2><p class="muted">{{ $class->published_at?'Alasan koreksi dicatat pada riwayat. Nilai disembunyikan dari KHS sampai diterbitkan kembali.':'Sistem memeriksa kelengkapan nilai seluruh peserta sebelum publikasi.' }}</p><form method="post" action="{{ route('publish',$class->id) }}" data-busy>@csrf<input type="hidden" name="aksi" value="{{ $class->published_at?'koreksi':'terbit' }}">
@if($class->published_at)<div class="field"><label for="alasan">Alasan koreksi</label><textarea id="alasan" name="alasan" required maxlength="1000" rows="2"></textarea></div>
@endif<button class="button {{ $class->published_at?'secondary':'primary' }} icon-action" aria-label="{{ $class->published_at?'Buka koreksi nilai':'Terbitkan nilai' }}" title="{{ $class->published_at?'Buka koreksi nilai':'Terbitkan nilai' }}"><x-icon :name="$class->published_at?'edit':'send'"/><span class="control-label sr-only">{{ $class->published_at?'Buka koreksi nilai':'Terbitkan nilai' }}</span></button><p class="form-status" role="status"></p></form></section>
@endif
@endsection
