@extends('layouts.app')
@section('title','Persetujuan KRS')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Tinjauan akademik</p><h1>Persetujuan KRS</h1><p class="muted">Periksa rencana studi dan berikan keputusan yang dapat ditelusuri.</p></div></div><form class="filterbar" method="get"><div class="field compact grow"><label for="q">Cari mahasiswa</label><input id="q" name="q" value="{{ request('q') }}"></div><div class="field compact"><label for="status">Status KRS</label><select id="status" name="status">
@foreach(['diajukan'=>'Menunggu keputusan','disetujui'=>'Disetujui','dikembalikan'=>'Dikembalikan','draf'=>'Draf'] as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>
@endforeach</select></div><button class="button secondary icon-action" aria-label="Terapkan" title="Terapkan"><x-icon name="filter"/><span class="control-label sr-only">Terapkan</span></button></form>
@forelse($rows as $row)<section class="panel approval"><div class="approval-heading"><div><h2>{{ $row->nama }}</h2><p class="muted">{{ $row->npm }} · {{ $row->periode }}</p></div><div><strong>{{ $row->classes->sum('sks') }} SKS</strong><span class="status neutral">{{ ucfirst($row->status) }}</span></div></div><div class="table-wrap"><table><thead><tr><th>Mata kuliah</th><th>Kelas</th><th>SKS</th></tr></thead><tbody>
@foreach($row->classes as $c)<tr><td>{{ $c->nama }}</td><td>{{ $c->kode }}</td><td>{{ $c->sks }}</td></tr>
@endforeach</tbody></table></div>
@if($row->status==='diajukan')<form method="post" action="{{ route('review',$row->id) }}" data-busy>@csrf<div class="field"><label for="note-{{ $row->id }}">Catatan keputusan (wajib jika dikembalikan)</label><textarea name="catatan" id="note-{{ $row->id }}" rows="2" maxlength="1000"></textarea></div><div class="heading-actions"><button class="button primary icon-action" name="aksi" value="setujui" aria-label="Setujui KRS" title="Setujui KRS"><x-icon name="check"/><span class="control-label sr-only">Setujui KRS</span></button><button class="button secondary icon-action" name="aksi" value="kembalikan" aria-label="Kembalikan untuk revisi" title="Kembalikan untuk revisi"><x-icon name="return"/><span class="control-label sr-only">Kembalikan untuk revisi</span></button></div><p class="form-status" role="status"></p></form>
@elseif($row->catatan)<p class="muted">Catatan: {{ $row->catatan }}</p>
@endif</section>
@empty<section class="panel empty"><h2>Semua pengajuan sudah tertangani</h2><p>Tidak ada KRS dengan status yang dipilih.</p></section>
@endforelse{{ $rows->links() }}
@endsection
