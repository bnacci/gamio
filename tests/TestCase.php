<?php
namespace Bnacci\Gamio\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use WithFaker;

    protected function getPackageProviders($app)
    {
        return [
            \Bnacci\Gamio\Providers\GamioProvider::class,
        ];
    }

    // <-- CORRETO: "getEnvironmentSetUp" (U maiúsculo)
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 1) Rode as migrations "core" do Laravel primeiro (users, password_resets...)
        try {
            $this->loadLaravelMigrations(['--database' => 'testing']);
            Artisan::call('migrate', ['--database' => 'testing', '--force' => true]);
        } catch (\Throwable $e) {
            dump('Erro rodando migrations do framework: ' . $e->getMessage());
        }

        // 2) Certifica que users existe (fallback seguro)
        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 3) Registra/roda as migrations do seu pacote (ajuste o caminho se necessário)
        $migrationsPath = __DIR__ . '/../database/migrations';
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);

            try {
                // Tenta rodar explicitamente as migrations do pacote
                Artisan::call('migrate', ['--database' => 'testing', '--force' => true]);
            } catch (\Throwable $e) {
                dump('Erro rodando migrations do pacote: ' . $e->getMessage());
            }
        }

        // 4) Debug leve: listar migrations rodadas e tabelas
        try {
            $ran = DB::table('migrations')->pluck('migration')->toArray();
            // dump('migrations rodadas:', $ran);

            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
            $names  = array_map(fn($t) => (array) $t, $tables);
            // dump('tabelas presentes:', $names);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
