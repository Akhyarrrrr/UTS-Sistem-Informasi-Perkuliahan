<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.connections.mysql.database') !== 'uts_perkuliahan_test') {
            throw new \RuntimeException('Database khusus tes wajib.');
        }
    }

    public function test_every_master_can_create_update_and_delete_unused_records(): void
    {
        $this->actingAs(User::where('email', 'admin@demo.test')->firstOrFail());
        $ids = [];
        $payloads = [];
        $create = function (string $entity, array $payload) use (&$ids, &$payloads) {
            $this->post('/admin/'.$entity, $payload)->assertSessionHasNoErrors()->assertRedirect();
            $id = DB::table($entity)->max('id');
            $ids[$entity] = $id;
            $payloads[$entity] = $payload;
            $this->assertDatabaseHas($entity, ['id' => $id]);

            return $id;
        };
        $faculty = $create('fakultas', ['kode' => 'UJI-F', 'nama' => 'Fakultas pengujian']);
        $program = $create('prodi', ['kode' => 'UJI-P', 'nama' => 'Program pengujian', 'fakultas_id' => $faculty, 'batas_sks' => 24]);
        $account = $create('users', ['name' => 'Akun uji', 'email' => 'crud@demo.test', 'role' => 'mahasiswa', 'password' => 'PasswordUji2026!']);
        $this->assertTrue(Hash::check('PasswordUji2026!', DB::table('users')->where('id', $account)->value('password')));
        $create('mahasiswa', ['npm' => '00012345', 'nama' => 'Mahasiswa uji', 'prodi_id' => $program, 'angkatan' => 2026, 'user_id' => $account]);
        $lecturer = $create('dosen', ['kode' => 'UJI-D', 'nama' => 'Dosen uji', 'prodi_id' => $program, 'user_id' => null]);
        $course = $create('matakuliah', ['kode' => 'UJI-M', 'nama' => 'Mata kuliah uji', 'prodi_id' => $program, 'sks' => 3]);
        $term = $create('periode', ['nama' => '2027/2028 Ganjil uji', 'tahun_mulai' => 2027, 'semester' => 'Ganjil', 'krs_mulai' => '2027-09-01', 'krs_selesai' => '2027-09-30', 'aktif' => false]);
        $room = $create('ruang', ['kode' => 'UJI-R', 'nama' => 'Ruang uji', 'kapasitas' => 10]);
        $class = $create('kelas', ['kode' => 'UJI', 'matakuliah_id' => $course, 'periode_id' => $term, 'dosen_id' => $lecturer, 'kapasitas' => 10]);
        $this->assertSame(4, DB::table('komponen_nilai')->where('kelas_id', $class)->count());
        $create('jadwal', ['kelas_id' => $class, 'ruang_id' => $room, 'hari' => 1, 'mulai' => '08:00', 'selesai' => '09:00', 'mode' => 'Luring', 'tautan' => null]);
        $create('skala_nilai', ['periode_id' => $term, 'huruf' => 'B', 'minimum' => 70, 'angka' => 3]);
        foreach ($ids as $entity => $id) {
            $data = $payloads[$entity];
            if (isset($data['nama'])) {
                $data['nama'] .= ' revisi';
            }if ($entity === 'users') {
                $data['password'] = '';
            }
            $this->put('/admin/'.$entity.'/'.$id, $data)->assertSessionHasNoErrors();
        }
        $data = $payloads['ruang'];
        $data['kapasitas'] = 9;
        $this->put('/admin/ruang/'.$room, $data)->assertSessionHasErrors('akademik');
        foreach (['skala_nilai', 'jadwal', 'kelas', 'mahasiswa', 'dosen', 'matakuliah', 'periode', 'ruang', 'users', 'prodi', 'fakultas'] as $entity) {
            $this->delete('/admin/'.$entity.'/'.$ids[$entity])->assertSessionHasNoErrors();
            $this->assertDatabaseMissing($entity, ['id' => $ids[$entity]]);
        }
        $this->assertSame(0, DB::table('komponen_nilai')->where('kelas_id', $class)->count());
    }
}
