<?php

namespace App\Http\Controllers;

use App\Services\Academic;
use App\Services\MasterData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use stdClass;

class MasterController extends Controller
{
    private function entity(Request $request): string
    {
        return explode('.', $request->route()->getName())[1];
    }

    public function index(Request $request): View
    {
        $entity = $this->entity($request);
        [$title,$fields] = MasterData::definition($entity);
        $query = DB::table($entity);
        if ($request->filled('q')) {
            $query->where(function ($searchQuery) use ($fields, $request) {
                foreach ($fields as $key => $field) {
                    if (in_array($field[1], ['text', 'email'])) {
                        $searchQuery->orWhere($key, 'like', '%'.$request->string('q').'%');
                    }
                }
            });
        }
        foreach (['prodi_id', 'periode_id', 'role'] as $filter) {
            if (isset($fields[$filter]) && $request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }
        $rows = $query->orderByDesc('id')->paginate(12)->withQueryString();
        $options = [];
        foreach ($fields as $key => $field) {
            $options[$key] = MasterData::options($field[1]);
        }

        return view('master.index', compact('entity', 'title', 'fields', 'rows', 'options'));
    }

    public function create(Request $request): View
    {
        return $this->form($request, null);
    }

    public function edit(Request $request, int $id): View
    {
        return $this->form($request, DB::table($this->entity($request))->find($id) ?? abort(404));
    }

    private function form(Request $request, ?stdClass $row): View
    {
        $entity = $this->entity($request);
        [$title,$fields] = MasterData::definition($entity);
        $options = [];
        foreach ($fields as $key => $field) {
            $options[$key] = MasterData::options($field[1]);
        }

        return view('master.form', compact('entity', 'title', 'fields', 'options', 'row'));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, null);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        DB::table($this->entity($request))->find($id) ?? abort(404);

        return $this->save($request, $id);
    }

    private function save(Request $request, ?int $id): RedirectResponse
    {
        $entity = $this->entity($request);
        $data = $request->validate(MasterData::rules($entity, $id));
        if ($entity === 'users') {
            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        }
        try {
            DB::transaction(function () use ($entity, $data, $id) {
                if ($entity === 'users') {
                    // Lock account mutations together to protect the last administrator.
                    DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
                }
                if ($id) {
                    DB::table($entity)->where('id', $id)->lockForUpdate()->first();
                }
                MasterData::guards($entity, $data, $id);
                if ($entity === 'periode' && ! empty($data['aktif'])) {
                    DB::table('periode')->update(['aktif' => false]);
                }
                if ($id) {
                    DB::table($entity)->where('id', $id)->update($data);
                    $saved = $id;
                } else {
                    $saved = DB::table($entity)->insertGetId($data);
                }
                if ($entity === 'kelas' && ! $id) {
                    foreach (['Tugas' => 20, 'Kuis' => 10, 'UTS' => 30, 'UAS' => 40] as $name => $weight) {
                        DB::table('komponen_nilai')->insert(['kelas_id' => $saved, 'nama' => $name, 'bobot' => $weight]);
                    }
                }
                Academic::audit($id ? 'Ubah data' : 'Tambah data', $entity, $saved, $data);
            }, 3);
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ['23000', '45000', 'HY000'])) {
                Academic::fail('Data belum dapat disimpan. Periksa keunikan kode, hubungan, dan batas nilainya.');
            }throw $e;
        }

        return redirect()->route('master.'.$entity.'.index')->with('success', 'Data berhasil disimpan.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $entity = $this->entity($request);
        try {
            DB::transaction(function () use ($entity, $id) {
                if ($entity === 'users') {
                    DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
                }
                $old = DB::table($entity)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
                if ($entity === 'users' && ($id === auth()->id() || ($old->role === 'admin' && DB::table('users')->where('role', 'admin')->count() === 1))) {
                    Academic::fail('Akun yang sedang digunakan atau admin terakhir tidak dapat dihapus.');
                }
                if ($entity === 'skala_nilai') {
                    MasterData::guards($entity, (array) $old, $id);
                    if ((float) $old->minimum === 0.0) {
                        Academic::fail('Skala minimum nol diperlukan untuk menghitung seluruh rentang nilai.');
                    }
                }
                if ($entity === 'periode' && $old->aktif) {
                    Academic::fail('Tentukan periode utama lain sebelum menghapus periode ini.');
                }
                if ($entity === 'jadwal') {
                    DB::table('kelas')->where('id', $old->kelas_id)->lockForUpdate()->first();
                    if (Academic::reserved($old->kelas_id)) {
                        Academic::fail('Jadwal kelas dengan peserta dikunci.');
                    }
                }
                if ($entity === 'kelas' && ! DB::table('krs_detail')->where('kelas_id', $id)->exists() && ! DB::table('jadwal')->where('kelas_id', $id)->exists() && ! DB::table('pertemuan')->where('kelas_id', $id)->exists()) {
                    DB::table('komponen_nilai')->where('kelas_id', $id)->delete();
                }
                DB::table($entity)->where('id', $id)->delete();
                Academic::audit('Hapus data', $entity, $id);
            }, 3);
        } catch (QueryException $e) {
            Academic::fail('Data masih digunakan oleh catatan lain sehingga tidak dapat dihapus.');
        }

        return back()->with('success', 'Data yang belum digunakan berhasil dihapus.');
    }
}
