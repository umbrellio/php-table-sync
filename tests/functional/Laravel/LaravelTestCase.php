<?php

namespace Umbrellio\TableSync\Tests\functional\Laravel;

use Umbrellio\TableSync\Tests\FunctionalTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

abstract class LaravelTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__ . '/../database/factories');
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
