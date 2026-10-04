@extends('layouts.app')
@section('title','Presensi saya')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Catatan kehadiran</p><h1>Presensi saya</h1><p class="muted">{{ $student->nama }} · {{ $student->npm }} · catatan dari dosen pengampu.</p></div></div><section class="panel"><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Mata kuliah</th><th>Pertemuan</th><th>Topik</th><th>Presensi</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row->tanggal }}</td><td>{{ $row->matakuliah }}</td><td>{{ $row->nomor }}</td><td>{{ $row->topik }}</td><td><span class="status {{ $row->status==='Hadir'?'positive':'neutral' }}">{{ $row->status ?? 'Belum dicatat' }}</span></td></tr>
@empty<tr><td colspan="5"><div class="empty"><h3>Presensi belum tersedia</h3><p>Catatan muncul setelah dosen membuat pertemuan kelas.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $rows->links() }}</section>
@endsection
