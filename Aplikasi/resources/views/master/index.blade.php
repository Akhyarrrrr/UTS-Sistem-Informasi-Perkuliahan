@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="page-heading"><div><p class="eyebrow">Data akademik</p><h1>{{ $title }}</h1><p class="muted">Kelola catatan {{ strtolower($title) }} dan hubungan datanya.</p></div><a class="button primary" href="{{ route('master.'.$entity.'.create') }}">Tambah {{ strtolower($title) }}</a></div>
<form class="filterbar" method="get"><div class="field compact grow"><label for="q">Cari {{ strtolower($title) }}</label><input name="q" id="q" value="{{ request('q') }}" placeholder="Nama atau kode"></div>
@foreach(['prodi_id','periode_id','role'] as $filter)
@if(isset($fields[$filter]))<div class="field compact"><label for="{{ $filter }}">{{ $fields[$filter][0] }}</label><select id="{{ $filter }}" name="{{ $filter }}"><option value="">Semua</option>
@foreach($options[$filter] as $key=>$label)<option value="{{ $key }}" @selected(request($filter)==$key)>{{ $label }}</option>
@endforeach</select></div>
@endif
@endforeach<button class="button secondary">Terapkan</button>
@if(request()->query())<a class="text-button" href="{{ route('master.'.$entity.'.index') }}">Reset</a>
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
@endforeach<td><div class="row-actions"><a href="{{ route('master.'.$entity.'.edit',$row->id) }}">Ubah</a><form method="post" action="{{ route('master.'.$entity.'.destroy',$row->id) }}" data-confirm="Hapus catatan ini? Data yang masih digunakan akan dilindungi.">@csrf @method('DELETE')<button class="text-button danger">Hapus</button></form></div></td></tr>
@empty<tr><td colspan="{{ count($fields)+1 }}"><div class="empty"><h3>Belum ada catatan yang sesuai</h3><p>Ubah pencarian atau tambahkan data {{ strtolower($title) }}.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $rows->links() }}</section>
@endsection
