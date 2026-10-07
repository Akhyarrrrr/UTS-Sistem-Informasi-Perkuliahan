@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">Data akademik</p><h1>{{ $title }}</h1><p class="muted">Kelola catatan {{ strtolower($title) }} dan hubungan datanya.</p></div><a class="button primary icon-action" href="{{ route('master.'.$entity.'.create') }}" aria-label="Tambah {{ strtolower($title) }}" title="Tambah {{ strtolower($title) }}"><x-icon name="plus"/><span class="control-label sr-only">Tambah {{ strtolower($title) }}</span></a></div>
<form class="filterbar" method="get"><div class="field compact grow"><label for="q">Cari {{ strtolower($title) }}</label><input name="q" id="q" value="{{ request('q') }}" placeholder="Nama atau kode"></div>
@foreach(['prodi_id','periode_id','role'] as $filter)
@if(isset($fields[$filter]))<div class="field compact"><label for="{{ $filter }}">{{ $fields[$filter][0] }}</label><select id="{{ $filter }}" name="{{ $filter }}"><option value="">Semua</option>
@foreach($options[$filter] as $key=>$label)<option value="{{ $key }}" @selected(request($filter)==$key)>{{ $label }}</option>
@endforeach</select></div>
@endif
@endforeach<button class="button secondary icon-action" aria-label="Terapkan" title="Terapkan"><x-icon name="filter"/><span class="control-label sr-only">Terapkan</span></button>
@if(request()->query())<a class="text-button icon-action" href="{{ route('master.'.$entity.'.index') }}" aria-label="Reset" title="Reset"><x-icon name="reset"/><span class="control-label sr-only">Reset</span></a>
@endif</form>
<section class="panel"><div class="panel-heading"><h2>Daftar {{ strtolower($title) }}</h2><span class="muted">{{ $rows->total() }} catatan</span></div><div class="table-wrap"><table><thead><tr>
@foreach($fields as $key=>$f)
@if($key!=='password')<th>{{ $f[0] }}</th>
@endif
@endforeach<th class="actions-column">Tindakan</th></tr></thead><tbody>
@forelse($rows as $row)<tr>
@foreach($fields as $key=>$f)
@if($key!=='password')<td>
@if($f[1]==='boolean'){{ $row->$key?'Ya':'Tidak' }}
@elseif($options[$key]!==null){{ $options[$key][$row->$key] ?? 'Belum dihubungkan' }}
@else{{ $row->$key ?? 'Belum diisi' }}
@endif</td>
@endif
@endforeach<td><div class="row-actions"><a class="icon-action" href="{{ route('master.'.$entity.'.edit',$row->id) }}" aria-label="Ubah" title="Ubah"><x-icon name="edit"/><span class="sr-only">Ubah</span></a><form method="post" action="{{ route('master.'.$entity.'.destroy',$row->id) }}" data-busy data-confirm="Hapus catatan ini? Data yang masih digunakan akan dilindungi.">@csrf @method('DELETE')<button class="text-button danger icon-action" aria-label="Hapus" title="Hapus"><x-icon name="trash"/><span class="control-label sr-only">Hapus</span></button></form></div></td></tr>
@empty<tr><td colspan="{{ count($fields)+1 }}"><div class="empty"><h3>Belum ada catatan yang sesuai</h3><p>Ubah pencarian atau tambahkan data {{ strtolower($title) }}.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $rows->links() }}</section>
@endsection
