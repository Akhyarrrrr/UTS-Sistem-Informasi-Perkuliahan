<div class="field compact"><label for="periode">Periode akademik</label><select name="periode" id="periode">
@foreach($periods as $p)<option value="{{ $p->id }}" @selected(($period?->id ?? request('periode'))==$p->id)>{{ $p->nama }}</option>
@endforeach</select></div>
