<?php

namespace App\Http\Controllers;

use App\Services\Academic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicController extends Controller
{
    public function dashboard(Request $request): View
    {
        $period = Academic::period();
        $user = $request->user();
        $student = null;
        $krs = null;
        $ips = null;
        $ipk = null;
        $classes = Academic::classQuery()->when($period, fn ($query) => $query->where('k.periode_id', $period->id));
        if ($user->role === 'dosen') {
            $classes->where('d.user_id', $user->id);
        }
        if ($user->role === 'mahasiswa') {
            $student = Academic::student($user);
            $krs = DB::table('krs')->where('mahasiswa_id', $student->id)->where('periode_id', $period?->id)->first();
            $ids = $krs ? DB::table('krs_detail')->where('krs_id', $krs->id)->pluck('kelas_id') : collect();
            $classes->whereIn('k.id', $ids);
            $ips = Academic::transcript($student->id, $period?->id);
            $ipk = Academic::transcript($student->id);
        }
        $classes = $classes->orderBy('m.nama')->get();
        $pending = DB::table('krs')->where('status', 'diajukan')->count();
        $counts = ['mahasiswa' => DB::table('mahasiswa')->count(), 'dosen' => DB::table('dosen')->count(), 'matakuliah' => DB::table('matakuliah')->count(), 'kelas' => $classes->count()];
        $recent = DB::table('activity_log as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')->when($user->role !== 'admin', fn ($query) => $query->where('a.user_id', $user->id))->select('a.*', 'u.name')->orderByDesc('a.id')->limit(6)->get();
        $schedules = $this->schedules($classes->pluck('id')->all());

        return view('academic.dashboard', compact('period', 'user', 'student', 'krs', 'ips', 'ipk', 'classes', 'pending', 'counts', 'recent', 'schedules'));
    }

    private function schedules(array $classes): Collection
    {
        return DB::table('jadwal as j')->join('kelas as k', 'k.id', '=', 'j.kelas_id')->join('matakuliah as m', 'm.id', '=', 'k.matakuliah_id')->leftJoin('ruang as r', 'r.id', '=', 'j.ruang_id')->whereIn('j.kelas_id', $classes)->select('j.*', 'm.nama as matakuliah', 'k.kode as kelas', 'r.nama as ruang')->orderBy('j.hari')->orderBy('j.mulai')->get();
    }

    public function classes(Request $request): View
    {
        $period = Academic::period($request->integer('periode') ?: null);
        $query = Academic::classQuery()->when($period, fn ($query) => $query->where('k.periode_id', $period->id));
        if ($request->user()->role === 'dosen') {
            $query->where('d.user_id', $request->user()->id);
        }
        if ($request->filled('q')) {
            $query->where('m.nama', 'like', '%'.$request->string('q').'%');
        }if ($request->filled('status')) {
            $request->input('status') === 'terbit' ? $query->whereNotNull('k.published_at') : $query->whereNull('k.published_at');
        }
        $classes = $query->orderBy('m.nama')->paginate(12)->withQueryString();
        foreach ($classes as $class) {
            $class->reserved = Academic::reserved($class->id);
        }

        return view('academic.classes', compact('period', 'classes'));
    }

    public function grades(Request $request, int $id): View
    {
        $class = Academic::teachingClass($request->user(), $id);
        $participants = Academic::participants($id);
        $components = DB::table('komponen_nilai')->where('kelas_id', $id)->orderBy('id')->get();
        $scores = DB::table('nilai_komponen')->whereIn('krs_detail_id', $participants->pluck('id'))->get()->groupBy('krs_detail_id');
        foreach ($participants as $participant) {
            $participant->hasil = Academic::result($participant->id);
        }

        return view('academic.grades', compact('class', 'participants', 'components', 'scores'));
    }

    public function saveGrades(Request $request, int $id): RedirectResponse
    {
        Academic::teachingClass($request->user(), $id);
        $data = $request->validate(['nilai' => ['required', 'array'], 'nilai.*' => ['array'], 'nilai.*.*' => ['nullable', 'numeric', 'between:0,100']]);
        Academic::saveScores($id, $data['nilai']);

        return back()->with('success', 'Nilai tersimpan. Nilai kosong tetap berstatus belum dinilai.');
    }

    public function weights(Request $request, int $id): RedirectResponse
    {
        Academic::teachingClass($request->user(), $id);
        $data = $request->validate(['bobot' => ['required', 'array'], 'bobot.*' => ['required', 'numeric', 'gt:0', 'max:100']]);
        DB::transaction(function () use ($data, $id) {
            $class = DB::table('kelas')->where('id', $id)->lockForUpdate()->first();
            if ($class->first_published_at) {
                Academic::fail('Bobot kelas yang pernah menerbitkan nilai dikunci.');
            }if (abs(array_sum($data['bobot']) - 100) > 0.001) {
                Academic::fail('Jumlah bobot harus 100%.');
            }$ids = DB::table('komponen_nilai')->where('kelas_id', $id)->pluck('id')->all();
            if (count($ids) !== count($data['bobot']) || array_diff(array_map('intval', array_keys($data['bobot'])), $ids)) {
                Academic::fail('Komponen nilai tidak sesuai.');
            }foreach ($data['bobot'] as $key => $value) {
                DB::table('komponen_nilai')->where('id', $key)->update(['bobot' => $value]);
            }Academic::audit('Ubah bobot nilai', 'kelas', $id, $data);
        });

        return back()->with('success', 'Bobot penilaian diperbarui.');
    }

    public function publish(Request $request, int $id): RedirectResponse
    {
        Academic::teachingClass($request->user(), $id);
        $data = $request->validate(['aksi' => ['required', 'in:terbit,koreksi'], 'alasan' => ['nullable', 'string', 'max:1000']]);
        Academic::publish($id, $data['aksi'] === 'terbit', $data['alasan'] ?? null);

        return back()->with('success', $data['aksi'] === 'terbit' ? 'Nilai diterbitkan ke KHS mahasiswa.' : 'Publikasi dibuka untuk koreksi. Alasan tercatat pada riwayat.');
    }

    public function krs(Request $request): View
    {
        $student = Academic::student($request->user());
        $period = Academic::period($request->integer('periode') ?: null) ?? abort(404);
        $krs = DB::table('krs')->where('mahasiswa_id', $student->id)->where('periode_id', $period->id)->first();
        $selected = $krs ? DB::table('krs_detail')->where('krs_id', $krs->id)->pluck('kelas_id')->all() : [];
        $classes = Academic::classQuery()->where('k.periode_id', $period->id)->where('m.prodi_id', $student->prodi_id)->orderBy('m.nama')->get();
        foreach ($classes as $class) {
            $class->reserved = Academic::reserved($class->id);
            $class->jadwal = $this->schedules([$class->id]);
        }$limit = DB::table('prodi')->where('id', $student->prodi_id)->value('batas_sks');

        return view('academic.krs', compact('student', 'period', 'krs', 'selected', 'classes', 'limit'));
    }

    public function saveKrs(Request $request): RedirectResponse
    {
        $student = Academic::student($request->user());
        $data = $request->validate(['periode_id' => ['required', 'integer', 'exists:periode,id'], 'kelas' => ['nullable', 'array'], 'kelas.*' => ['integer', 'distinct', 'exists:kelas,id']]);
        Academic::saveKrs($student->id, (int) $data['periode_id'], array_map('intval', $data['kelas'] ?? []));

        return redirect()->route('krs', ['periode' => $data['periode_id']])->with('success', 'Draf KRS disimpan. Ajukan untuk memesan kursi dan meminta persetujuan.');
    }

    public function submitKrs(Request $request, int $id): RedirectResponse
    {
        Academic::submitKrs($id, Academic::student($request->user())->id);

        return back()->with('success', 'KRS diajukan. Kursi dipesan sambil menunggu persetujuan admin.');
    }

    public function approvals(Request $request): View
    {
        $status = $request->input('status', 'diajukan');
        $query = DB::table('krs as r')->join('mahasiswa as m', 'm.id', '=', 'r.mahasiswa_id')->join('periode as p', 'p.id', '=', 'r.periode_id')->select('r.*', 'm.nama', 'm.npm', 'p.nama as periode');
        if (in_array($status, ['draf', 'diajukan', 'disetujui', 'dikembalikan'])) {
            $query->where('r.status', $status);
        }if ($request->filled('q')) {
            $query->where('m.nama', 'like', '%'.$request->string('q').'%');
        }
        $rows = $query->orderByDesc('r.updated_at')->paginate(12)->withQueryString();
        foreach ($rows as $row) {
            $row->classes = DB::table('krs_detail as d')->join('kelas as k', 'k.id', '=', 'd.kelas_id')->join('matakuliah as m', 'm.id', '=', 'k.matakuliah_id')->where('d.krs_id', $row->id)->select('m.nama', 'm.sks', 'k.kode')->get();
        }

        return view('academic.approvals', compact('rows', 'status'));
    }

    public function review(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['aksi' => ['required', 'in:setujui,kembalikan'], 'catatan' => ['nullable', 'string', 'max:1000']]);
        Academic::reviewKrs($id, $data['aksi'], $data['catatan'] ?? null);

        return back()->with('success', 'Keputusan KRS tersimpan.');
    }

    public function khs(Request $request): View
    {
        $student = $request->user()->role === 'mahasiswa' ? Academic::student($request->user()) : (DB::table('mahasiswa')->find($request->integer('mahasiswa')) ?? DB::table('mahasiswa')->first() ?? abort(404));
        $period = Academic::period($request->integer('periode') ?: null);
        $result = Academic::transcript($student->id, $period?->id);
        $cumulative = Academic::transcript($student->id);

        return view('academic.khs', compact('student', 'period', 'result', 'cumulative'));
    }

    public function schedule(Request $request): View
    {
        $period = Academic::period($request->integer('periode') ?: null);
        $classes = Academic::classQuery()->when($period, fn ($query) => $query->where('k.periode_id', $period->id));
        if ($request->user()->role === 'dosen') {
            $classes->where('d.user_id', $request->user()->id);
        }
        if ($request->user()->role === 'mahasiswa') {
            $student = Academic::student($request->user());
            $ids = DB::table('krs_detail as d')->join('krs as r', 'r.id', '=', 'd.krs_id')->where('r.mahasiswa_id', $student->id)->where('r.status', 'disetujui')->pluck('d.kelas_id');
            $classes->whereIn('k.id', $ids);
        }$schedules = $this->schedules($classes->pluck('k.id')->all());

        return view('academic.schedule', compact('period', 'schedules'));
    }

    public function attendance(Request $request, int $id): View
    {
        $class = Academic::teachingClass($request->user(), $id);
        $meetings = DB::table('pertemuan')->where('kelas_id', $id)->orderBy('nomor')->get();
        $participants = Academic::participants($id);
        $meeting = $request->integer('pertemuan') ? DB::table('pertemuan')->where('kelas_id', $id)->find($request->integer('pertemuan')) : $meetings->last();
        $attendance = $meeting ? DB::table('presensi')->where('pertemuan_id', $meeting->id)->pluck('status', 'krs_detail_id') : collect();

        return view('academic.attendance', compact('class', 'meetings', 'participants', 'meeting', 'attendance'));
    }

    public function createMeeting(Request $request, int $id): RedirectResponse
    {
        Academic::teachingClass($request->user(), $id);
        $data = $request->validate(['nomor' => ['required', 'integer', 'between:1,32'], 'tanggal' => ['required', 'date'], 'topik' => ['required', 'string', 'max:160']]);
        $meeting = DB::transaction(function () use ($id, $data): int {
            DB::table('kelas')->where('id', $id)->lockForUpdate()->first();
            if (DB::table('pertemuan')->where('kelas_id', $id)->where('nomor', $data['nomor'])->lockForUpdate()->exists()) {
                Academic::fail('Nomor pertemuan sudah digunakan.');
            }
            $meeting = DB::table('pertemuan')->insertGetId($data + ['kelas_id' => $id]);
            Academic::audit('Tambah pertemuan', 'pertemuan', $meeting, $data);

            return $meeting;
        }, 3);

        return redirect()->route('attendance', [$id, 'pertemuan' => $meeting])->with('success', 'Pertemuan ditambahkan.');
    }

    public function saveAttendance(Request $request, int $id): RedirectResponse
    {
        $meeting = DB::table('pertemuan')->find($id) ?? abort(404);
        Academic::teachingClass($request->user(), $meeting->kelas_id);
        $data = $request->validate(['presensi' => ['required', 'array'], 'presensi.*' => ['nullable', 'in:Hadir,Izin,Sakit,Alpa']]);
        $ids = Academic::participants($meeting->kelas_id)->pluck('id')->all();
        DB::transaction(function () use ($data, $ids, $id) {
            foreach ($data['presensi'] as $detail => $status) {
                if (! in_array((int) $detail, $ids, true)) {
                    Academic::fail('Peserta tidak berasal dari kelas pertemuan.');
                }if (! $status) {
                    DB::table('presensi')->where('pertemuan_id', $id)->where('krs_detail_id', $detail)->delete();
                } else {
                    DB::table('presensi')->updateOrInsert(['pertemuan_id' => $id, 'krs_detail_id' => $detail], ['status' => $status]);
                }
            }Academic::audit('Simpan presensi', 'pertemuan', $id);
        });

        return back()->with('success', 'Presensi tersimpan. Kolom kosong tetap belum dicatat.');
    }

    public function myAttendance(Request $request): View
    {
        $student = Academic::student($request->user());
        $rows = DB::table('krs_detail as d')->join('krs as k', 'k.id', '=', 'd.krs_id')->join('kelas as c', 'c.id', '=', 'd.kelas_id')->join('matakuliah as m', 'm.id', '=', 'c.matakuliah_id')->join('pertemuan as p', 'p.kelas_id', '=', 'c.id')->leftJoin('presensi as a', fn ($j) => $j->on('a.pertemuan_id', '=', 'p.id')->on('a.krs_detail_id', '=', 'd.id'))->where('k.mahasiswa_id', $student->id)->where('k.status', 'disetujui')->select('p.*', 'm.nama as matakuliah', 'a.status')->orderByDesc('p.tanggal')->paginate(16);

        return view('academic.my-attendance', compact('rows', 'student'));
    }

    public function activity(Request $request): View
    {
        $rows = DB::table('activity_log as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')->select('a.*', 'u.name')->orderByDesc('a.id')->paginate(20);
        $participants = DB::table('krs_detail as d')->join('krs as r', 'r.id', '=', 'd.krs_id')->join('mahasiswa as m', 'm.id', '=', 'r.mahasiswa_id')->pluck('m.nama', 'd.id');
        $components = DB::table('komponen_nilai')->pluck('nama', 'id');

        return view('academic.activity', compact('rows', 'participants', 'components'));
    }

    public function export(Request $request, string $kind): StreamedResponse
    {
        $rows = [];
        $headers = [];
        if ($kind === 'khs') {
            $student = $request->user()->role === 'mahasiswa' ? Academic::student($request->user()) : DB::table('mahasiswa')->find($request->integer('mahasiswa'));
            abort_unless($student && in_array($request->user()->role, ['admin', 'mahasiswa']), 403);
            $headers = ['NPM', 'Nama', 'Periode', 'Kode MK', 'Mata kuliah', 'SKS', 'Nilai akhir', 'Huruf', 'Angka mutu', 'Status'];
            foreach (Academic::transcript($student->id, $request->integer('periode') ?: null)['rows'] as $value) {
                $rows[] = [$student->npm, $student->nama, $value->periode, $value->kode, $value->nama, $value->sks, $value->hasil['nilai'] ?? '', $value->hasil['huruf'] ?? '', $value->hasil['angka'] ?? '', $value->hasil ? 'Terbit' : 'Belum terbit'];
            }
        } elseif ($kind === 'kelas') {
            abort_unless(in_array($request->user()->role, ['admin', 'dosen']), 403);
            $class = Academic::teachingClass($request->user(), $request->integer('kelas'));
            $headers = ['NPM', 'Nama', 'Mata kuliah', 'Kelas', 'Nilai akhir', 'Huruf', 'Publikasi'];
            foreach (Academic::participants($class->id) as $participant) {
                $result = Academic::result($participant->id);
                $rows[] = [$participant->npm, $participant->nama, $class->matakuliah, $class->kode, $result['nilai'] ?? '', $result['huruf'] ?? '', $class->published_at ? 'Terbit' : 'Draf'];
            }
        } else {
            abort(404);
        }

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ',', '"', '');
            foreach ($rows as $row) {
                $row = array_map(fn ($value) => is_string($value) && preg_match('/^[=+@-]/', $value) ? "'".$value : $value, $row);
                fputcsv($out, $row, ',', '"', '');
            }fclose($out);
        }, $kind.'-simulasi.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
