@extends('layouts.app')
@section('title','Riwayat perubahan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Pengawasan akademik</p><h1>Riwayat perubahan</h1><p class="muted">Tindakan data, keputusan KRS, dan alasan koreksi nilai.</p></div></div><section class="panel"><div class="table-wrap"><table><thead><tr><th>Waktu (WIB)</th><th>Pengguna</th><th>Tindakan</th><th>Catatan</th></tr></thead><tbody>
@forelse($rows as $row)
@php($detail=json_decode($row->detail??'{}',true))
<tr><td class="nowrap">{{ $row->created_at }}</td><td>{{ $row->name ?? 'Sistem' }}</td><td><strong>{{ $row->aksi }}</strong><small>{{ $row->entitas }} #{{ $row->record_id ?? 'sistem' }}</small></td><td>{{ $detail['alasan'] ?? $detail['catatan'] ?? 'Perubahan data tercatat' }}
@if(!empty($detail['perubahan']))
<details><summary>{{ count($detail['perubahan']) }} nilai berubah</summary><ul>
@foreach($detail['perubahan'] as $change)
<li>{{ $participants[$change['peserta_id']] ?? 'Peserta tersimpan' }} · {{ $components[$change['komponen_id']] ?? 'Komponen tersimpan' }}: {{ $change['sebelum'] === null ? 'Belum dinilai' : $change['sebelum'] }} → {{ $change['sesudah'] === null ? 'Belum dinilai' : $change['sesudah'] }}</li>
@endforeach
</ul></details>
@endif
</td></tr>
@empty<tr><td colspan="4"><div class="empty"><p>Belum ada perubahan.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $rows->links() }}</section>
@endsection
