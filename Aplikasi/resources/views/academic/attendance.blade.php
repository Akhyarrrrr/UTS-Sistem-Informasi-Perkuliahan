@extends('layouts.app')
@section('title','Presensi '.$class->kode_mk)
@section('content')
<div class="page-heading"><div><p class="eyebrow">{{ $class->periode }} / {{ $class->kode_mk }} / {{ $class->kode }}</p><h1>Presensi perkuliahan</h1><p class="muted">{{ $class->matakuliah }} · {{ $class->dosen }}</p></div><a class="button secondary" href="{{ route('grades',$class->id) }}">Buka penilaian</a></div>
<details class="panel settings"><summary>Tambah pertemuan</summary><form method="post" action="{{ route('meetings.store',$class->id) }}" data-busy>@csrf<div class="form-grid"><div class="field"><label for="nomor">Pertemuan ke</label><input type="number" name="nomor" id="nomor" min="1" max="32" value="{{ ($meetings->max('nomor')??0)+1 }}" required></div><div class="field"><label for="tanggal">Tanggal</label><input type="date" name="tanggal" id="tanggal" value="{{ today()->toDateString() }}" required></div><div class="field full-field"><label for="topik">Topik pertemuan</label><input name="topik" id="topik" maxlength="160" required></div></div><button class="button secondary">Simpan pertemuan</button><p class="form-status" role="status"></p></form></details>
@if($meetings->count())<form class="filterbar" method="get"><div class="field compact grow"><label for="pertemuan">Pertemuan</label><select name="pertemuan" id="pertemuan">
@foreach($meetings as $m)<option value="{{ $m->id }}" @selected($meeting?->id===$m->id)>{{ $m->nomor }} · {{ $m->tanggal }} · {{ $m->topik }}</option>
@endforeach</select></div><button class="button secondary">Lihat presensi</button></form>
@endif
@if($meeting)<form class="panel" method="post" action="{{ route('attendance.save',$meeting->id) }}" data-busy>@csrf<div class="panel-heading"><div><h2>{{ $meeting->topik }}</h2><p class="muted">Pertemuan {{ $meeting->nomor }} · {{ $meeting->tanggal }}</p></div><span class="muted">{{ $attendance->count() }} / {{ $participants->count() }} tercatat</span></div><div class="table-wrap"><table><thead><tr><th>NPM</th><th>Mahasiswa</th><th>Status kehadiran</th></tr></thead><tbody>
@forelse($participants as $p)<tr><td class="course-code">{{ $p->npm }}</td><td>{{ $p->nama }}</td><td><select aria-label="Presensi {{ $p->nama }}" name="presensi[{{ $p->id }}]"><option value="">Belum dicatat</option>
@foreach(['Hadir','Izin','Sakit','Alpa'] as $status)<option value="{{ $status }}" @selected($attendance->get($p->id)===$status)>{{ $status }}</option>
@endforeach</select></td></tr>
@empty<tr><td colspan="3"><div class="empty"><p>Belum ada peserta disetujui.</p></div></td></tr>
@endforelse</tbody></table></div>
@if($participants->count())<div class="form-footer"><p class="muted">Belum dicatat berbeda dari alpa.</p><button class="button primary">Simpan presensi</button></div>
@endif<p class="form-status" role="status"></p></form>
@else<section class="panel empty"><h2>Pertemuan belum dibuat</h2><p>Tambahkan pertemuan untuk mulai mencatat presensi.</p></section>
@endif
@endsection
