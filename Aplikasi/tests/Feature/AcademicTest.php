<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Academic;
use App\Services\MasterData;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AcademicTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.connections.mysql.database') !== 'uts_perkuliahan_test') {
            throw new \RuntimeException('Tes wajib menggunakan database khusus tes.');
        }
    }

    private function asRole(string $email): static
    {
        return $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    private function detail(int $class, int $student = 1): int
    {
        return DB::table('krs_detail as d')->join('krs as r', 'r.id', '=', 'd.krs_id')->where('d.kelas_id', $class)->where('r.mahasiswa_id', $student)->value('d.id');
    }

    private function fillScores(int $class, float $score): void
    {
        $values = [];
        foreach (Academic::participants($class) as $p) {
            foreach (DB::table('komponen_nilai')->where('kelas_id', $class)->pluck('id') as $c) {
                $values[$p->id][$c] = $score;
            }
        }
        Academic::saveScores($class, $values);
    }

    private function invalid(callable $action, string $contains): void
    {
        try {
            $action();
            $this->fail('Operasi seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString($contains, implode(' ', array_merge(...array_values($e->errors()))));
        }
    }

    public function test_login_hash_logout_and_guest_redirect(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $user = User::where('email', 'admin@demo.test')->first();
        $user->password = Hash::make('TestPassword2026!');
        $user->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'TestPassword2026!'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_home_is_public_for_guests_and_every_role_without_local_credentials(): void
    {
        $this->assertSame('/', route('home', absolute: false));
        $this->get('/')->assertOk()->assertSee('Rencana')->assertDontSee('admin@demo.test');
        foreach (['admin@demo.test', 'dosen@demo.test', 'mahasiswa@demo.test'] as $email) {
            $this->asRole($email)->get('/')->assertOk()->assertSee('Buka ruang kerja')->assertDontSee('admin@demo.test');
        }
    }

    public function test_repeated_login_is_limited_and_password_is_never_flashed(): void
    {
        $email = 'throttle-audit@demo.test';
        $key = 'login:'.hash('sha256', $email.'|127.0.0.1');
        RateLimiter::clear($key);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong'])->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        }
        $this->post('/login', ['email' => $email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        RateLimiter::clear($key);
    }

    public function test_duplicate_meeting_is_a_validation_error_and_records_one_audit(): void
    {
        $this->asRole('dosen@demo.test');
        $data = ['nomor' => 32, 'tanggal' => '2026-10-06', 'topik' => 'Pertemuan uji duplikasi'];
        $this->post('/kelas/4/pertemuan', $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->post('/kelas/4/pertemuan', $data)->assertSessionHasErrors('akademik');
        $this->assertSame(1, DB::table('pertemuan')->where('kelas_id', 4)->where('nomor', 32)->count());
        $meeting = DB::table('pertemuan')->where('kelas_id', 4)->where('nomor', 32)->value('id');
        $this->assertSame(1, DB::table('activity_log')->where('entitas', 'pertemuan')->where('record_id', $meeting)->count());
    }

    public function test_admin_pages_all_render_and_have_database_content(): void
    {
        $this->asRole('admin@demo.test');
        foreach (['dashboard', 'jadwal', 'kelas', 'khs', 'admin/persetujuan-krs', 'admin/riwayat', 'kelas/4/nilai', 'kelas/4/presensi'] as $url) {
            $this->get('/'.$url)->assertOk();
        }
        foreach (array_keys(MasterData::definitions()) as $entity) {
            $this->get('/admin/'.$entity)->assertOk();
            $this->get('/admin/'.$entity.'/create')->assertOk();
            $this->get('/admin/'.$entity.'/1/edit')->assertOk();
        }
    }

    public function test_student_pages_and_cross_record_visibility(): void
    {
        $this->asRole('mahasiswa@demo.test');
        foreach (['dashboard', 'jadwal', 'krs', 'khs', 'presensi-saya'] as $url) {
            $this->get('/'.$url)->assertOk();
        }
        $this->get('/khs?mahasiswa=2')->assertSee('260820701100010')->assertDontSee('Nadia Rahma');
        $this->get('/admin/mahasiswa')->assertForbidden();
        $this->get('/kelas/4/nilai')->assertForbidden();
        $this->post('/krs/3/ajukan')->assertForbidden();
    }

    public function test_lecturer_access_requires_own_class(): void
    {
        $this->asRole('dosen@demo.test');
        $this->get('/dashboard')->assertOk();
        $this->get('/kelas')->assertOk();
        $this->get('/kelas/4/nilai')->assertOk();
        $this->get('/kelas/4/presensi')->assertOk();
        $this->get('/kelas/5/nilai')->assertForbidden();
        $this->post('/kelas/5/publikasi', ['aksi' => 'terbit'])->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/khs')->assertForbidden();
    }

    public function test_master_crud_and_protected_deletion(): void
    {
        $this->asRole('admin@demo.test');
        $payload = ['kode' => 'TEST', 'nama' => 'Fakultas Uji'];
        $this->post('/admin/fakultas', $payload)->assertSessionHasNoErrors();
        $id = DB::table('fakultas')->where('kode', 'TEST')->value('id');
        $this->put('/admin/fakultas/'.$id, ['kode' => 'TEST', 'nama' => 'Fakultas Revisi'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('fakultas', ['id' => $id, 'nama' => 'Fakultas Revisi']);
        $this->post('/admin/fakultas', $payload)->assertSessionHasErrors('kode');
        $this->delete('/admin/fakultas/'.$id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('fakultas', ['id' => $id]);
        $this->delete('/admin/fakultas/1')->assertSessionHasErrors('akademik');
        $this->assertDatabaseHas('fakultas', ['id' => 1]);
        $this->delete('/admin/users/1')->assertSessionHasErrors('akademik');
    }

    public function test_krs_full_workflow_and_return_releases_capacity(): void
    {
        $this->asRole('mhs11@demo.test');
        $this->post('/krs', ['periode_id' => 2, 'kelas' => [5, 6]])->assertSessionHasNoErrors();
        $krs = DB::table('krs')->where('mahasiswa_id', 11)->first();
        $before = Academic::reserved(5);
        $this->post('/krs/'.$krs->id.'/ajukan')->assertSessionHasNoErrors();
        $this->assertSame($before + 1, Academic::reserved(5));
        $this->post('/krs', ['periode_id' => 2, 'kelas' => [7]])->assertSessionHasErrors('akademik');
        $this->asRole('admin@demo.test');
        $this->post('/admin/persetujuan-krs/'.$krs->id, ['aksi' => 'kembalikan'])->assertSessionHasErrors('akademik');
        $this->post('/admin/persetujuan-krs/'.$krs->id, ['aksi' => 'kembalikan', 'catatan' => 'Periksa pilihan'])->assertSessionHasNoErrors();
        $this->assertSame($before, Academic::reserved(5));
        $this->asRole('mhs11@demo.test');
        $this->post('/krs', ['periode_id' => 2, 'kelas' => [5]])->assertSessionHasNoErrors();
        $this->post('/krs/'.$krs->id.'/ajukan')->assertSessionHasNoErrors();
        $this->asRole('admin@demo.test');
        $this->post('/admin/persetujuan-krs/'.$krs->id, ['aksi' => 'setujui'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('krs', ['id' => $krs->id, 'status' => 'disetujui']);
    }

    public function test_capacity_and_empty_submission_rejected(): void
    {
        DB::table('kelas')->where('id', 5)->update(['kapasitas' => 1]);
        $id = Academic::saveKrs(11, 2, [5]);
        $this->invalid(fn () => Academic::submitKrs($id, 11), 'penuh');
        $id = Academic::saveKrs(11, 2, []);
        $this->invalid(fn () => Academic::submitKrs($id, 11), 'minimal satu');
    }

    public function test_krs_duplicate_course_sks_schedule_period_and_prodi_rules(): void
    {
        DB::table('kelas')->where('id', 1)->update(['published_at' => null]);
        $this->invalid(fn () => Academic::saveKrs(11, 2, [1, 8]), 'Satu mata kuliah');
        DB::table('prodi')->where('id', 1)->update(['batas_sks' => 3]);
        $this->invalid(fn () => Academic::saveKrs(11, 2, [5, 6]), 'SKS');
        DB::table('prodi')->where('id', 1)->update(['batas_sks' => 24]);
        DB::table('jadwal')->where('kelas_id', 6)->update(['hari' => 5, 'mulai' => '10:00', 'selesai' => '12:00']);
        $this->invalid(fn () => Academic::saveKrs(11, 2, [5, 6]), 'bertabrakan');
        $this->invalid(fn () => Academic::saveKrs(11, 1, [9]), 'ditutup');
        $this->invalid(fn () => Academic::saveKrs(11, 2, [9]), 'periode');
        $this->invalid(fn () => Academic::saveKrs(13, 2, [5]), 'program studi');
        $this->asRole('mhs11@demo.test')->post('/krs', ['periode_id' => 2, 'kelas' => [5, 5]])->assertSessionHasErrors('kelas.0');
    }

    public function test_schedule_conflicts_and_used_schedule_lock(): void
    {
        $this->asRole('admin@demo.test');
        $payload = ['kelas_id' => 6, 'hari' => 1, 'mulai' => '09:00', 'selesai' => '10:00', 'mode' => 'Luring', 'ruang_id' => 1, 'tautan' => null];
        $this->post('/admin/jadwal', $payload)->assertSessionHasErrors('akademik');
        $payload['ruang_id'] = 3;
        $payload['hari'] = 3;
        $this->post('/admin/jadwal', $payload)->assertSessionHasErrors('akademik');
        $payload['kelas_id'] = 4;
        $payload['hari'] = 6;
        $this->post('/admin/jadwal', $payload)->assertSessionHasErrors('akademik');
    }

    public function test_null_scores_are_pending_and_zero_is_a_real_grade(): void
    {
        $detail = $this->detail(4);
        $this->assertNull(Academic::result($detail));
        $this->fillScores(4, 0);
        $this->assertSame('E', Academic::result($detail)['huruf']);
        $component = DB::table('komponen_nilai')->where('kelas_id', 4)->value('id');
        Academic::saveScores(4, [$detail => [$component => null]]);
        $this->assertNull(Academic::result($detail));
        $this->invalid(fn () => Academic::publish(4, true), 'Lengkapi');
    }

    public static function boundaries(): array
    {
        return [[100, 'A'], [85, 'A'], [84.99, 'B+'], [80, 'B+'], [79.99, 'B'], [70, 'B'], [69.99, 'C+'], [65, 'C+'], [64.99, 'C'], [60, 'C'], [59.99, 'D'], [50, 'D'], [49.99, 'E'], [0, 'E']];
    }

    #[DataProvider('boundaries')]
    public function test_every_grade_boundary(float $value, string $grade): void
    {
        $this->fillScores(4, $value);
        $result = Academic::result($this->detail(4));
        $this->assertEquals($value, $result['nilai']);
        $this->assertSame($grade, $result['huruf']);
    }

    public function test_weighted_values_ips_and_latest_published_repeat(): void
    {
        $this->assertEquals(88.5, Academic::result($this->detail(1))['nilai']);
        $semester = Academic::transcript(1, 2);
        $this->assertEquals(3.75, $semester['ip']);
        $this->assertSame(8, $semester['sks']);
        $this->assertSame(1, $semester['pending']);
        $cumulative = Academic::transcript(1);
        $this->assertEquals(3.55, $cumulative['ip']);
        $this->assertSame(11, $cumulative['sks']);
    }

    public function test_publishing_correction_audit_and_weights_remain_locked(): void
    {
        $this->fillScores(4, 90);
        Academic::publish(4, true);
        $this->assertEquals(3.82, Academic::transcript(1, 2)['ip']);
        $this->invalid(fn () => Academic::saveScores(4, [$this->detail(4) => []]), 'Batalkan');
        $this->invalid(fn () => Academic::publish(4, false), 'alasan');
        Academic::publish(4, false, 'Koreksi UAS berdasarkan lembar jawaban');
        $this->asRole('admin@demo.test');
        $weights = DB::table('komponen_nilai')->where('kelas_id', 4)->pluck('bobot', 'id')->all();
        $this->post('/admin/kelas/4/bobot', ['bobot' => $weights])->assertSessionHasErrors('akademik');
        $this->fillScores(4, 80);
        Academic::publish(4, true);
        $this->assertDatabaseHas('activity_log', ['aksi' => 'Buka koreksi nilai', 'record_id' => 4]);
        $this->assertEquals(3.68, Academic::transcript(1, 2)['ip']);
    }

    public function test_weight_sum_and_component_record_manipulation(): void
    {
        $this->asRole('admin@demo.test');
        $components = DB::table('komponen_nilai')->where('kelas_id', 4)->pluck('id');
        $bad = array_fill_keys($components->all(), 20);
        $this->post('/admin/kelas/4/bobot', ['bobot' => $bad])->assertSessionHasErrors('akademik');
        $this->invalid(fn () => Academic::saveScores(4, [$this->detail(1) => [$components[0] => 80]]), 'Peserta');
        $foreign = DB::table('komponen_nilai')->where('kelas_id', 1)->value('id');
        $this->invalid(fn () => Academic::saveScores(4, [$this->detail(4) => [$foreign => 80]]), 'Komponen');
        $this->invalid(fn () => Academic::saveScores(4, [$this->detail(4) => [$components[0] => 101]]), 'rentang');
    }

    public function test_presensi_is_scoped_and_missing_distinct_from_alpa(): void
    {
        $this->asRole('dosen@demo.test');
        $meeting = DB::table('pertemuan')->where('kelas_id', 4)->value('id');
        $detail = $this->detail(4);
        $this->post('/pertemuan/'.$meeting.'/presensi', ['presensi' => [$this->detail(1) => 'Hadir']])->assertSessionHasErrors('akademik');
        $this->post('/pertemuan/'.$meeting.'/presensi', ['presensi' => [$detail => 'Alpa']])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('presensi', ['pertemuan_id' => $meeting, 'krs_detail_id' => $detail, 'status' => 'Alpa']);
        $this->post('/pertemuan/'.$meeting.'/presensi', ['presensi' => [$detail => '']])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('presensi', ['pertemuan_id' => $meeting, 'krs_detail_id' => $detail]);
    }

    public function test_csv_unicode_formula_and_access(): void
    {
        DB::table('mahasiswa')->where('id', 1)->update(['nama' => '=SUM(1,2)']);
        $this->asRole('mahasiswa@demo.test');
        $response = $this->get('/ekspor/khs?mahasiswa=2&periode=2')->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString("'=SUM(1,2)", $content);
        $this->assertStringContainsString('260820701100010', $content);
        $this->assertStringNotContainsString('SIM2026', $content);
        $this->get('/ekspor/kelas?kelas=4')->assertForbidden();
    }

    public function test_mysql_rejects_invalid_range_fk_and_cross_class_update(): void
    {
        foreach ([
            fn () => DB::table('nilai_komponen')->where('krs_detail_id', $this->detail(1))->update(['nilai' => 101]),
            fn () => DB::table('kelas')->where('id', 1)->update(['dosen_id' => 999999]),
            fn () => DB::table('nilai_komponen')->where('krs_detail_id', $this->detail(1))->limit(1)->update(['komponen_nilai_id' => DB::table('komponen_nilai')->where('kelas_id', 4)->value('id')]),
        ] as $operation) {
            try {
                $operation();
                $this->fail('MySQL seharusnya menolak.');
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }
}
