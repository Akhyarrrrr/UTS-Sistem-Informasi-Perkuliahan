<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterData
{
    public static function definitions(): array
    {
        return [
            'fakultas' => ['Fakultas', ['kode' => ['Kode', 'text'], 'nama' => ['Nama fakultas', 'text']]],
            'prodi' => ['Program studi', ['kode' => ['Kode', 'text'], 'nama' => ['Nama program studi', 'text'], 'fakultas_id' => ['Fakultas', 'fakultas'], 'batas_sks' => ['Batas SKS', 'number']]],
            'mahasiswa' => ['Mahasiswa', ['npm' => ['NPM', 'text'], 'nama' => ['Nama mahasiswa', 'text'], 'prodi_id' => ['Program studi', 'prodi'], 'angkatan' => ['Tahun angkatan', 'number'], 'user_id' => ['Akun mahasiswa (opsional)', 'users:mahasiswa']]],
            'dosen' => ['Dosen', ['kode' => ['Kode dosen', 'text'], 'nama' => ['Nama dosen', 'text'], 'prodi_id' => ['Program studi', 'prodi'], 'user_id' => ['Akun dosen (opsional)', 'users:dosen']]],
            'matakuliah' => ['Mata kuliah', ['kode' => ['Kode mata kuliah', 'text'], 'nama' => ['Nama mata kuliah', 'text'], 'prodi_id' => ['Program studi', 'prodi'], 'sks' => ['SKS', 'number']]],
            'periode' => ['Periode akademik', ['nama' => ['Nama periode', 'text'], 'tahun_mulai' => ['Tahun mulai', 'number'], 'semester' => ['Semester', 'semester'], 'krs_mulai' => ['KRS dibuka', 'date'], 'krs_selesai' => ['KRS ditutup', 'date'], 'aktif' => ['Periode utama', 'boolean']]],
            'ruang' => ['Ruang', ['kode' => ['Kode ruang', 'text'], 'nama' => ['Nama ruang', 'text'], 'kapasitas' => ['Kapasitas', 'number']]],
            'kelas' => ['Kelas perkuliahan', ['kode' => ['Kode kelas', 'text'], 'matakuliah_id' => ['Mata kuliah', 'matakuliah'], 'periode_id' => ['Periode', 'periode'], 'dosen_id' => ['Dosen pengampu', 'dosen'], 'kapasitas' => ['Kapasitas', 'number']]],
            'jadwal' => ['Jadwal', ['kelas_id' => ['Kelas', 'kelas'], 'hari' => ['Hari', 'hari'], 'mulai' => ['Mulai', 'time'], 'selesai' => ['Selesai', 'time'], 'mode' => ['Pelaksanaan', 'mode'], 'ruang_id' => ['Ruang (wajib untuk luring)', 'ruang'], 'tautan' => ['Tautan pertemuan (opsional)', 'url']]],
            'skala_nilai' => ['Skala nilai', ['periode_id' => ['Periode', 'periode'], 'huruf' => ['Nilai huruf', 'text'], 'minimum' => ['Nilai minimum', 'decimal'], 'angka' => ['Angka mutu', 'decimal']]],
            'users' => ['Pengguna', ['name' => ['Nama pengguna', 'text'], 'email' => ['Email', 'email'], 'role' => ['Peran', 'role'], 'password' => ['Kata sandi (kosongkan saat tidak diubah)', 'password']]],
        ];
    }

    public static function definition(string $table): array
    {
        return self::definitions()[$table] ?? abort(404);
    }

    public static function options(string $type): ?array
    {
        $fixed = ['semester' => ['Ganjil' => 'Ganjil', 'Genap' => 'Genap'], 'hari' => [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'], 'mode' => ['Luring' => 'Luring', 'Daring' => 'Daring'], 'role' => ['admin' => 'Admin', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa']];
        if (isset($fixed[$type])) {
            return $fixed[$type];
        }
        if (str_starts_with($type, 'users:')) {
            return DB::table('users')->where('role', substr($type, 6))->orderBy('name')->pluck('name', 'id')->all();
        }
        if ($type === 'kelas') {
            return Academic::classQuery()->orderByDesc('k.id')->get()->mapWithKeys(fn ($r) => [$r->id => $r->kode_mk.' / '.$r->kode.' / '.$r->periode])->all();
        }
        if (in_array($type, ['fakultas', 'prodi', 'matakuliah', 'periode', 'dosen', 'ruang'])) {
            return DB::table($type)->orderBy('nama')->pluck('nama', 'id')->all();
        }

        return null;
    }

    public static function rules(string $table, ?int $id): array
    {
        $rules = [];
        foreach (self::definition($table)[1] as $key => $field) {
            $type = $field[1];
            $nullable = in_array($key, ['user_id', 'ruang_id', 'tautan']) || ($key === 'password' && $id);
            $r = [$nullable ? 'nullable' : 'required'];
            if ($key === 'password') {
                $r = array_merge($r, ['string', 'min:10', 'max:100']);
            } elseif (in_array($type, ['number', 'decimal'])) {
                $r = array_merge($r, [$type === 'number' ? 'integer' : 'numeric', 'min:0']);
            } elseif ($type === 'date') {
                $r[] = 'date';
            } elseif ($type === 'time') {
                $r[] = 'date_format:H:i';
            } elseif ($type === 'boolean') {
                $r[] = 'boolean';
            } elseif ($type === 'email') {
                $r = array_merge($r, ['email', 'max:255']);
            } elseif ($type === 'url') {
                $r = array_merge($r, ['url:http,https', 'max:255']);
            } elseif (str_ends_with($key, '_id')) {
                $r = array_merge($r, ['integer', Rule::exists(explode(':', $type)[0], 'id')]);
            } elseif (self::options($type) !== null) {
                $r[] = Rule::in(array_keys(self::options($type)));
            } else {
                $r = array_merge($r, ['string', 'max:'.match ($key) {
                    'kode' => ($table === 'kelas' ? 12 : ($table === 'fakultas' || $table === 'prodi' ? 12 : 20)),'npm' => 20,'huruf' => 2,default => ($table === 'periode' ? 60 : 100)
                }]);
            }
            if (in_array($key, ['email', 'npm']) || ($key === 'kode' && $table !== 'kelas') || ($key === 'nama' && $table === 'periode') || $key === 'user_id') {
                $r[] = Rule::unique($table, $key)->ignore($id);
            }
            $rules[$key] = $r;
        }
        if ($table === 'matakuliah') {
            $rules['sks'] = ['required', 'integer', 'between:1,6'];
        }
        if ($table === 'prodi') {
            $rules['batas_sks'] = ['required', 'integer', 'between:1,30'];
        }
        if (in_array($table, ['ruang', 'kelas'])) {
            $rules['kapasitas'] = ['required', 'integer', 'between:1,500'];
        }
        if ($table === 'mahasiswa') {
            $rules['angkatan'] = ['required', 'integer', 'between:2000,2100'];
        }
        if ($table === 'periode') {
            $rules['tahun_mulai'] = ['required', 'integer', 'between:2000,2100'];
            $rules['krs_selesai'][] = 'after_or_equal:krs_mulai';
        }
        if ($table === 'jadwal') {
            $rules['selesai'][] = 'after:mulai';
            $rules['ruang_id'][] = 'required_if:mode,Luring';
        }
        if ($table === 'skala_nilai') {
            $rules['minimum'] = ['required', 'numeric', 'between:0,100'];
            $rules['angka'] = ['required', 'numeric', 'between:0,4'];
        }

        return $rules;
    }

    public static function guards(string $table, array $data, ?int $id): void
    {
        if ($table === 'users' && $id) {
            $old = DB::table('users')->find($id);
            if ($old->role !== $data['role'] && (DB::table('mahasiswa')->where('user_id', $id)->exists() || DB::table('dosen')->where('user_id', $id)->exists())) {
                Academic::fail('Lepaskan hubungan profil sebelum mengubah peran akun.');
            }
            if ($old->role === 'admin' && $data['role'] !== 'admin' && DB::table('users')->where('role', 'admin')->count() === 1) {
                Academic::fail('Sistem harus memiliki minimal satu admin.');
            }
            if ($id === auth()->id() && $data['role'] !== $old->role) {
                Academic::fail('Peran akun yang sedang digunakan tidak dapat diubah.');
            }
        }
        if (in_array($table, ['mahasiswa', 'dosen']) && ! empty($data['user_id']) && DB::table('users')->where('id', $data['user_id'])->value('role') !== $table) {
            Academic::fail('Peran akun harus sesuai dengan profil.');
        }
        if ($table === 'matakuliah' && $id && DB::table('kelas')->where('matakuliah_id', $id)->exists()) {
            $old = DB::table($table)->find($id);
            if ($old->sks != (int) $data['sks'] || $old->prodi_id != (int) $data['prodi_id']) {
                Academic::fail('SKS dan program studi mata kuliah yang sudah ditawarkan tidak dapat diubah.');
            }
        }
        if ($table === 'mahasiswa' && $id && DB::table('krs')->where('mahasiswa_id', $id)->exists() && DB::table($table)->where('id', $id)->value('prodi_id') != (int) $data['prodi_id']) {
            Academic::fail('Program studi mahasiswa dengan riwayat KRS tidak dapat diubah.');
        }
        if ($table === 'kelas' && $id) {
            $old = DB::table('kelas')->find($id);
            if (Academic::reserved($id) > (int) $data['kapasitas']) {
                Academic::fail('Kapasitas tidak boleh lebih kecil dari kursi yang sudah dipesan.');
            }
            if (DB::table('jadwal as j')->join('ruang as r', 'r.id', '=', 'j.ruang_id')->where('j.kelas_id', $id)->where('r.kapasitas', '<', $data['kapasitas'])->exists()) {
                Academic::fail('Kapasitas kelas melebihi kapasitas ruang pada jadwal.');
            }
            if (DB::table('krs_detail')->where('kelas_id', $id)->exists()) {
                foreach (['matakuliah_id', 'periode_id', 'dosen_id'] as $key) {
                    if ((int) $data[$key] != $old->$key) {
                        Academic::fail('Mata kuliah, periode, dan pengampu kelas dengan peserta tidak dapat diganti.');
                    }
                }
            }
        }
        if ($table === 'ruang' && $id && DB::table('jadwal as j')->join('kelas as k', 'k.id', '=', 'j.kelas_id')->where('j.ruang_id', $id)->where('k.kapasitas', '>', $data['kapasitas'])->exists()) {
            Academic::fail('Kapasitas ruang tidak boleh lebih kecil dari kelas yang menggunakan ruang ini.');
        }
        if ($table === 'jadwal') {
            $class = DB::table('kelas')->where('id', $data['kelas_id'])->lockForUpdate()->first();
            DB::table('dosen')->where('id', $class->dosen_id)->lockForUpdate()->first();
            if (! empty($data['ruang_id'])) {
                DB::table('ruang')->where('id', $data['ruang_id'])->lockForUpdate()->first();
            }
            if ($id) {
                $old = DB::table('jadwal')->find($id);
                if (Academic::reserved($old->kelas_id)) {
                    Academic::fail('Jadwal kelas dengan KRS diajukan/disetujui dikunci.');
                }
            }
            if (Academic::reserved($class->id)) {
                Academic::fail('Jadwal kelas dengan KRS diajukan/disetujui dikunci.');
            }
            if (! empty($data['ruang_id']) && DB::table('ruang')->where('id', $data['ruang_id'])->value('kapasitas') < $class->kapasitas) {
                Academic::fail('Kapasitas kelas melebihi kapasitas ruang.');
            }
            $overlap = DB::table('jadwal as j')->join('kelas as k', 'k.id', '=', 'j.kelas_id')->where('k.periode_id', $class->periode_id)->where('j.hari', $data['hari'])->where('j.mulai', '<', $data['selesai'])->where('j.selesai', '>', $data['mulai'])->when($id, fn ($q) => $q->where('j.id', '<>', $id))->where(function ($q) use ($class, $data) {
                $q->where('k.dosen_id', $class->dosen_id)->orWhere('k.id', $class->id);
                if (! empty($data['ruang_id'])) {
                    $q->orWhere('j.ruang_id', $data['ruang_id']);
                }
            })->lockForUpdate()->get(['j.id'])->isNotEmpty();
            if ($overlap) {
                Academic::fail('Jadwal bertabrakan dengan kelas, dosen, atau ruang yang sudah digunakan.');
            }
        }
        if ($table === 'skala_nilai') {
            $period = (int) $data['periode_id'];
            $used = DB::table('nilai_komponen as n')->join('komponen_nilai as c', 'c.id', '=', 'n.komponen_nilai_id')->join('kelas as k', 'k.id', '=', 'c.kelas_id')->where('k.periode_id', $period)->exists();
            if ($id) {
                $old = DB::table('skala_nilai')->find($id);
                $used = $used || DB::table('nilai_komponen as n')->join('komponen_nilai as c','c.id','=','n.komponen_nilai_id')->join('kelas as k','k.id','=','c.kelas_id')->where('k.periode_id',$old->periode_id)->exists();
            }
            if ($used) {
                Academic::fail('Skala nilai periode yang sudah digunakan untuk penilaian dikunci.');
            }
        }
    }
}
