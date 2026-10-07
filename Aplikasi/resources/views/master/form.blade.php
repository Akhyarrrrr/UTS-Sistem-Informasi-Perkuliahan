@extends('layouts.app')
@section('title',($row?'Ubah ':'Tambah ').strtolower($title))
@section('content')
<div class="page-heading"><div><p class="eyebrow">Data akademik / {{ $title }}</p><h1>{{ $row?'Ubah':'Tambah' }} {{ strtolower($title) }}</h1><p class="muted">Hubungkan data dengan referensi yang sesuai. Catatan yang digunakan tetap terlindungi.</p></div><a class="button secondary icon-action" href="{{ route('master.'.$entity.'.index') }}" aria-label="Kembali ke daftar" title="Kembali ke daftar"><x-icon name="back"/><span class="control-label sr-only">Kembali ke daftar</span></a></div>
<form class="panel form-panel" method="post" action="{{ $row?route('master.'.$entity.'.update',$row->id):route('master.'.$entity.'.store') }}" data-busy>@csrf
@if($row)@method('PUT')
@endif<div class="form-grid">
@foreach($fields as $key=>$f)
@php($value=old($key,$key==='password'?'':($row?->$key ?? ($key==='batas_sks'?24:($key==='aktif'?0:'')))))
@php($required=!in_array($key,['user_id','ruang_id','tautan']) && !($key==='password' && $row))
<div class="field"><label for="{{ $key }}">{{ $f[0] }}{{ $required?' *':'' }}</label>
@if($options[$key]!==null)<select id="{{ $key }}" name="{{ $key }}" @required($required) aria-invalid="{{ $errors->has($key)?'true':'false' }}" @if($errors->has($key))aria-describedby="error-{{ $key }}"@endif><option value="">Pilih {{ strtolower($f[0]) }}</option>
@foreach($options[$key] as $id=>$label)<option value="{{ $id }}" @selected($value==$id)>{{ $label }}</option>
@endforeach</select>
@elseif($f[1]==='boolean')<select name="{{ $key }}" id="{{ $key }}"><option value="0" @selected(!$value)>Tidak</option><option value="1" @selected($value)>Ya</option></select>
@else<input name="{{ $key }}" id="{{ $key }}" type="{{ $f[1]==='decimal'?'number':$f[1] }}" value="{{ $f[1]==='time'?substr((string)$value,0,5):$value }}" @required($required) aria-invalid="{{ $errors->has($key)?'true':'false' }}" @if($errors->has($key))aria-describedby="error-{{ $key }}"@endif
@if($f[1]==='decimal')step="0.01"
@endif
@if($f[1]==='password')autocomplete="new-password" minlength="10"
@endif>
@endif @error($key)<p class="field-error" id="error-{{ $key }}">{{ $message }}</p>@enderror
@if($key==='password')<p class="field-hint">Minimal 10 karakter. Gunakan kata sandi berbeda untuk akun produksi.</p>
@endif</div>
@endforeach</div><div class="form-footer"><p class="muted">Data simulasi · perubahan dicatat pada riwayat</p><button class="button primary icon-action" aria-label="Simpan {{ strtolower($title) }}" title="Simpan {{ strtolower($title) }}"><x-icon name="save"/><span class="control-label sr-only">Simpan {{ strtolower($title) }}</span></button></div><p class="form-status" role="status"></p></form>
@endsection
