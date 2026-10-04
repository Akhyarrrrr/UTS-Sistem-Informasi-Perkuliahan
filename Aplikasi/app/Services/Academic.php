<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Academic
{
    public static function fail(string $message): never
    {
        throw ValidationException::withMessages(['akademik' => $message]);
    }

    public static function audit(string $action, string $entity, ?int $id = null, array $detail = []): void
    {
        unset($detail['password'],$detail['remember_token']);
        DB::table('activity_log')->insert(['user_id' => auth()->id(), 'aksi' => $action, 'entitas' => $entity, 'record_id' => $id, 'detail' => json_encode($detail), 'created_at' => now()]);
    }

    public static function period(?int $id = null)
    {
        return $id ? DB::table('periode')->find($id) : DB::table('periode')->orderByDesc('aktif')->orderByDesc('tahun_mulai')->orderByDesc('id')->first();
    }

    public static function student(User $user)
    {
        return DB::table('mahasiswa')->where('user_id', $user->id)->first() ?? abort(403, 'Profil mahasiswa belum dihubungkan.');
    }

    public static function classQuery()
    {
        return DB::table('kelas as k')->join('matakuliah as m', 'm.id', '=', 'k.matakuliah_id')->join('dosen as d', 'd.id', '=', 'k.dosen_id')->join('periode as p', 'p.id', '=', 'k.periode_id')->select('k.*', 'm.nama as matakuliah', 'm.kode as kode_mk', 'm.sks', 'm.prodi_id', 'd.nama as dosen', 'd.user_id as dosen_user_id', 'p.nama as periode');
    }

    public static function teachingClass(User $user, int $id)
    {
        $class = self::classQuery()->where('k.id', $id)->first() ?? abort(404);
        abort_unless($user->role === 'admin' || ($user->role === 'dosen' && $class->dosen_user_id === $user->id), 403);

        return $class;
    }

    public static function participants(int $class)
    {
        return DB::table('krs_detail as kd')->join('krs as r', 'r.id', '=', 'kd.krs_id')->join('mahasiswa as m', 'm.id', '=', 'r.mahasiswa_id')->where('kd.kelas_id', $class)->where('r.status', 'disetujui')->select('kd.id', 'm.nama', 'm.npm', 'm.id as mahasiswa_id')->orderBy('m.nama')->get();
    }

    public static function reserved(int $class, ?int $exclude = null): int
    {
        $query = DB::table('krs_detail as d')->join('krs as r', 'r.id', '=', 'd.krs_id')->where('d.kelas_id', $class)->whereIn('r.status', ['diajukan', 'disetujui'])->when($exclude, fn ($q) => $q->where('r.id', '<>', $exclude));

        // A locking read sees reservations committed while this transaction waited for the class lock.
        return DB::transactionLevel() ? $query->lockForUpdate()->get(['d.id'])->count() : $query->count();
    }

    public static function window($period): void
    {
        if (! $period || today()->toDateString() < $period->krs_mulai || today()->toDateString() > $period->krs_selesai) {
            self::fail('Periode pengisian KRS sedang ditutup.');
        }
    }

    public static function validateSelections(int $student, int $period, array $classes): void
    {
        if (! $classes) {
            return;
        }
        $profile = DB::table('mahasiswa')->join('prodi', 'prodi.id', '=', 'mahasiswa.prodi_id')->where('mahasiswa.id', $student)->select('mahasiswa.prodi_id', 'prodi.batas_sks')->first();
        $rows = self::classQuery()->whereIn('k.id', $classes)->get();
        if ($rows->count() !== count($classes)) {
            self::fail('Kelas yang dipilih tidak ditemukan.');
        }
        foreach ($rows as $row) {
            if ($row->periode_id !== $period) {
                self::fail('Kelas harus berasal dari periode KRS yang dipilih.');
            }
            if ($row->prodi_id !== $profile->prodi_id) {
                self::fail('Kelas harus berasal dari program studi mahasiswa.');
            }
            if ($row->published_at) {
                self::fail('Kelas yang nilainya sudah terbit tidak menerima peserta baru.');
            }
        }
        if ($rows->pluck('matakuliah_id')->unique()->count() !== $rows->count()) {
            self::fail('Satu mata kuliah hanya boleh diambil pada satu kelas dalam periode yang sama.');
        }
        if ($rows->sum('sks') > $profile->batas_sks) {
            self::fail('Jumlah SKS melampaui batas program studi: '.$profile->batas_sks.' SKS.');
        }
        $schedules = DB::table('jadwal')->whereIn('kelas_id', $classes)->get();
        foreach ($schedules as $i => $a) {
            foreach ($schedules as $j => $b) {
                if ($i < $j && $a->hari === $b->hari && $a->mulai < $b->selesai && $b->mulai < $a->selesai) {
                    self::fail('Jadwal kelas yang dipilih bertabrakan.');
                }
            }
        }
    }

    public static function saveKrs(int $student, int $period, array $classes): int
    {
        return DB::transaction(function () use ($student, $period, $classes) {
            DB::table('mahasiswa')->where('id', $student)->lockForUpdate()->first();
            self::window(self::period($period));
            $krs = DB::table('krs')->where('mahasiswa_id', $student)->where('periode_id', $period)->lockForUpdate()->first();
            if ($krs && in_array($krs->status, ['diajukan', 'disetujui'])) {
                self::fail('KRS yang diajukan atau disetujui tidak dapat diubah.');
            }
            self::validateSelections($student, $period, $classes);
            $id = $krs?->id ?? DB::table('krs')->insertGetId(['mahasiswa_id' => $student, 'periode_id' => $period, 'status' => 'draf', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('krs_detail')->where('krs_id', $id)->delete();
            foreach ($classes as $class) {
                DB::table('krs_detail')->insert(['krs_id' => $id, 'kelas_id' => $class]);
            }
            DB::table('krs')->where('id', $id)->update(['status' => 'draf', 'updated_at' => now()]);
            self::audit('Simpan draf KRS', 'krs', $id, ['kelas' => $classes]);

            return $id;
        });
    }

    public static function submitKrs(int $id, int $student): void
    {
        DB::transaction(function () use ($id, $student) {
            $krs = DB::table('krs')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_unless($krs->mahasiswa_id === $student, 403);
            if ($krs->status !== 'draf') {
                self::fail('Hanya draf KRS yang dapat diajukan.');
            }
            self::window(self::period($krs->periode_id));
            $ids = DB::table('krs_detail')->where('krs_id', $id)->orderBy('kelas_id')->pluck('kelas_id')->all();
            if (! $ids) {
                self::fail('Pilih minimal satu kelas sebelum mengajukan KRS.');
            }
            $classes = DB::table('kelas')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            self::validateSelections($student, $krs->periode_id, $ids);
            foreach ($classes as $class) {
                if (self::reserved($class->id, $id) >= $class->kapasitas) {
                    self::fail('Kapasitas kelas '.$class->kode.' telah penuh. Pilih kelas lain.');
                }
            }
            DB::table('krs')->where('id', $id)->update(['status' => 'diajukan', 'catatan' => null, 'updated_at' => now()]);
            self::audit('Ajukan KRS', 'krs', $id);
        }, 3);
    }

    public static function reviewKrs(int $id, string $action, ?string $note): void
    {
        DB::transaction(function () use ($id, $action, $note) {
            $krs = DB::table('krs')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            if ($krs->status !== 'diajukan') {
                self::fail('KRS ini sudah ditinjau atau belum diajukan.');
            }
            $ids = DB::table('krs_detail')->where('krs_id', $id)->orderBy('kelas_id')->pluck('kelas_id')->all();
            $classes = DB::table('kelas')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            if ($action === 'setujui') {
                self::validateSelections($krs->mahasiswa_id, $krs->periode_id, $ids);
                foreach ($classes as $class) {
                    if (self::reserved($class->id, $id) >= $class->kapasitas) {
                        self::fail('Kapasitas kelas sudah penuh.');
                    }
                }
            } elseif (! $note) {
                self::fail('Tuliskan alasan pengembalian KRS.');
            }
            $status = $action === 'setujui' ? 'disetujui' : 'dikembalikan';
            DB::table('krs')->where('id', $id)->update(['status' => $status, 'catatan' => $note, 'updated_at' => now()]);
            self::audit('KRS '.$status, 'krs', $id, ['catatan' => $note]);
        }, 3);
    }

    public static function result(int $detail)
    {
        $d = DB::table('krs_detail')->find($detail) ?? abort(404);
        $components = DB::table('komponen_nilai')->where('kelas_id', $d->kelas_id)->get();
        $scores = DB::table('nilai_komponen')->where('krs_detail_id', $detail)->pluck('nilai', 'komponen_nilai_id');
        if (! $components->count() || abs((float) $components->sum('bobot') - 100) > 0.001) {
            return null;
        }
        $total = 0;
        foreach ($components as $c) {
            if (! isset($scores[$c->id])) {
                return null;
            } $total += (float) $scores[$c->id] * (float) $c->bobot / 100;
        }
        $total = round($total, 2);
        $period = DB::table('kelas')->where('id', $d->kelas_id)->value('periode_id');
        $scale = DB::table('skala_nilai')->where('periode_id', $period)->where('minimum', '<=', $total)->orderByDesc('minimum')->first();
        if (! $scale) {
            return null;
        }

        return ['nilai' => $total, 'huruf' => $scale->huruf, 'angka' => (float) $scale->angka];
    }

    public static function saveScores(int $class, array $values): void
    {
        DB::transaction(function () use ($class, $values) {
            $locked = DB::table('kelas')->where('id', $class)->lockForUpdate()->first();
            if ($locked->published_at) {
                self::fail('Batalkan publikasi dengan alasan sebelum memperbaiki nilai.');
            }
            $participants = self::participants($class)->pluck('id')->all();
            $components = DB::table('komponen_nilai')->where('kelas_id', $class)->pluck('id')->all();
            $changes = [];
            foreach ($values as $detail => $scores) {
                if (! in_array((int) $detail, $participants, true)) {
                    self::fail('Peserta tidak berasal dari kelas ini.');
                }
                foreach ($scores as $component => $value) {
                    if (! in_array((int) $component, $components, true)) {
                        self::fail('Komponen nilai tidak berasal dari kelas ini.');
                    }
                    if ($value !== null && $value !== '' && (! is_numeric($value) || $value < 0 || $value > 100)) {
                        self::fail('Nilai harus berada pada rentang 0 sampai 100.');
                    }
                    $before = DB::table('nilai_komponen')->where('krs_detail_id', $detail)->where('komponen_nilai_id', $component)->value('nilai');
                    $after = $value === null || $value === '' ? null : $value;
                    if ($before != $after || ($before === null) !== ($after === null)) {
                        $changes[] = ['peserta_id' => $detail, 'komponen_id' => $component, 'sebelum' => $before, 'sesudah' => $after];
                    }
                    DB::table('nilai_komponen')->updateOrInsert(['krs_detail_id' => $detail, 'komponen_nilai_id' => $component], ['nilai' => $after]);
                }
            }
            self::audit('Simpan nilai', 'kelas', $class, ['peserta' => count($values), 'perubahan' => $changes]);
        });
    }

    public static function publish(int $class, bool $publish, ?string $reason = null): void
    {
        DB::transaction(function () use ($class, $publish, $reason) {
            $row = DB::table('kelas')->where('id', $class)->lockForUpdate()->first();
            if ($publish) {
                if ($row->published_at) {
                    self::fail('Nilai kelas ini sudah diterbitkan.');
                }
                $participants = self::participants($class);
                if (! $participants->count()) {
                    self::fail('Kelas belum mempunyai peserta yang disetujui.');
                }
                foreach ($participants as $p) {
                    if (! self::result($p->id)) {
                        self::fail('Lengkapi seluruh nilai peserta dan pastikan bobot berjumlah 100% sebelum publikasi.');
                    }
                }
            } else {
                if (! $row->published_at) {
                    self::fail('Nilai kelas belum diterbitkan.');
                }
                if (! $reason) {
                    self::fail('Tuliskan alasan koreksi nilai terbit.');
                }
            }
            $update = ['published_at' => $publish ? now() : null];
            if ($publish && ! $row->first_published_at) {
                $update['first_published_at'] = now();
            }
            DB::table('kelas')->where('id', $class)->update($update);
            self::audit($publish ? 'Terbitkan nilai' : 'Buka koreksi nilai', 'kelas', $class, ['alasan' => $reason]);
        });
    }

    public static function transcript(int $student, ?int $period = null): array
    {
        $rows = DB::table('krs_detail as d')->join('krs as r', 'r.id', '=', 'd.krs_id')->join('kelas as k', 'k.id', '=', 'd.kelas_id')->join('matakuliah as m', 'm.id', '=', 'k.matakuliah_id')->join('periode as p', 'p.id', '=', 'k.periode_id')->where('r.mahasiswa_id', $student)->where('r.status', 'disetujui')->when($period, fn ($q) => $q->where('r.periode_id', $period))->select('d.id', 'm.id as mk_id', 'm.kode', 'm.nama', 'm.sks', 'k.published_at', 'p.nama as periode', 'p.tahun_mulai', 'p.semester')->orderBy('p.tahun_mulai')->orderByRaw("FIELD(p.semester,'Ganjil','Genap')")->get();
        $sks = 0;
        $points = 0;
        $pending = 0;
        $results = [];
        foreach ($rows as $row) {
            $row->hasil = $row->published_at ? self::result($row->id) : null;
            if (! $row->hasil) {
                $pending++;
            }
            $results[] = $row;
        }
        $counted = $period ? $results : array_values(array_reduce($results,function ($carry,$row) {
            if ($row->hasil) {
                $carry[$row->mk_id] = $row;
            }

return $carry;
        },[]));
        foreach ($counted as $row) {
            if ($row->hasil) {
                $sks += $row->sks;
                $points += $row->sks * $row->hasil['angka'];
            }
        }

        return ['rows' => $results, 'sks' => $sks, 'ip' => $sks ? round($points / $sks,2) : null, 'pending' => $pending];
    }
}
