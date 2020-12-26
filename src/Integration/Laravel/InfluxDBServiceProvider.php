<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use InfluxDB\Client as InfluxClient;
use InfluxDB\Client\Exception as ClientException;
use InfluxDB\Database as InfluxDB;
use InfluxDB\Driver\UDP;

class InfluxDBServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            $this->influxdbConfigPath() => config_path('influxdb.php'),
            $this->telegrafConfigPath() => config_path('telegraf.php'),
        ]);

        $this->mergeConfigFrom($this->influxdbConfigPath(), 'influxdb');
        $this->mergeConfigFrom($this->telegrafConfigPath(), 'telegraf');

        if (($logManager = $this->app->make('log')) instanceof LogManager) {
            $logManager->extend('influxdb', function ($app, array $config) {
                return (new InfluxDBLogChannel($app))($config);
            });

            $logManager->extend('telegraf', function ($app, array $config) {
                return (new TelegrafLogChannel($app))($config);
            });
        }
    }

    public function register()
    {
        $this->app->singleton(InfluxDB::class, function ($app) {
            try {
                $client = new InfluxClient(
                    config('influxdb.host'),
                    config('influxdb.port'),
                    config('influxdb.username'),
                    config('influxdb.password'),
                    config('influxdb.ssl'),
                    config('influxdb.verifySSL'),
                    config('influxdb.timeout')
                );
                if (config('influxdb.udp.enabled') === true) {
                    $client->setDriver(new UDP($client->getHost(), config('influxdb.udp.port')));
                }

                return $client->selectDB(config('influxdb.dbname'));
            } catch (ClientException $clientException) {
                return null;
            }
        });
    }

    public function provides(): array
    {
        return [InfluxDB::class];
    }

    protected function influxdbConfigPath(): string
    {
        return __DIR__ . '/config/influxdb.php';
    }

    protected function telegrafConfigPath(): string
    {
        return __DIR__ . '/config/telegraf.php';
    }
}
