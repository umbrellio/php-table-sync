<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

abstract class LaravelTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $params = $this->getConnectionParams();

        $app['config']->set('database.default', 'main');
        $app['config']->set('database.connections.main', [
            'driver' => 'pgsql',
            'host' => $params['host'],
            'port' => (int) $params['port'],
            'database' => $params['database'],
            'username' => $params['user'],
            'password' => $params['password'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]);
    }

    /**
     * @param Application $app
     */
    protected function resolveApplicationCore($app): void
    {
        parent::resolveApplicationCore($app);

        $app->detectEnvironment(function () {
            return 'testing';
        });
    }

    private function setUpDatabase($app): void
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/Laravel/migrations');
        $this->loadFactoriesUsing($app, __DIR__ . '/../../../database/Laravel/factories');

        $this->artisan('migrate');
    }

    private function getConnectionParams(): array
    {
        return [
            'driver' => $GLOBALS['db_type'] ?? 'pdo_pgsql',
            'user' => env('POSTGRES_USER', $GLOBALS['db_username']),
            'password' => env('POSTGRES_PASSWORD', $GLOBALS['db_password']),
            'host' => env('POSTGRES_HOST', $GLOBALS['db_host']),
            'database' => env('POSTGRES_DB', $GLOBALS['db_database']),
            'port' => env('POSTGRES_PORT', $GLOBALS['db_port']),
        ];
    }
}
