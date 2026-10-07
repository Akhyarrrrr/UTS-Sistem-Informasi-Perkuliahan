<?php

namespace Tests\Feature;

use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private array $previousEnvironment = [];

    public function createApplication(): Application
    {
        foreach (['VERCEL' => '1', 'VERCEL_URL' => 'preview.perkuliahan.test'] as $key => $value) {
            $this->previousEnvironment[$key] = $_ENV[$key] ?? null;
            $_ENV[$key] = $value;
        }

        return parent::createApplication();
    }

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.connections.mysql.database') !== 'uts_perkuliahan_test') {
            throw new RuntimeException('Database khusus tes wajib.');
        }
    }

    protected function tearDown(): void
    {
        SymfonyRequest::setTrustedHosts([]);
        SymfonyRequest::setTrustedProxies([], 0);
        foreach ($this->previousEnvironment as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
        parent::tearDown();
    }

    public function test_production_seeding_requires_a_private_password_before_writing_data(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $count = DB::table('users')->count();

        foreach ([null, 'short-password'] as $password) {
            config(['app.demo_password' => $password]);
            try {
                (new AcademicSeeder)->run();
                $this->fail('Seeder produksi harus menolak kata sandi yang belum disiapkan.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('minimal 16 karakter', $exception->getMessage());
            }
            $this->assertDatabaseCount('users', $count);
        }
    }

    public function test_https_proxy_preserves_the_real_client_ip_and_secure_form_url(): void
    {
        config(['app.url' => 'https://perkuliahan.test']);
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.42',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('http://preview.perkuliahan.test/login')
            ->assertOk()
            ->assertSee('https://preview.perkuliahan.test/login', escape: false);

        $this->assertSame('198.51.100.42', request()->ip());
    }

    public function test_local_account_activation_cannot_change_production_passwords(): void
    {
        $process = new Process([PHP_BINARY, base_path('scripts/activate-demo.php')], base_path(), ['APP_ENV' => 'production']);
        $process->run();

        $this->assertFalse($process->isSuccessful(), $process->getOutput());
        $this->assertStringContainsString('Hanya untuk database UTS lokal.', $process->getErrorOutput().$process->getOutput());
    }

    public function test_an_unrecognized_host_is_rejected_before_rendering_the_application(): void
    {
        config(['app.url' => 'https://perkuliahan.test']);
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('http://untrusted.example/login')->assertStatus(400);
    }
}
