<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Config as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Umbrellio\TableSync\Integration\Laravel\Console\PidManager;
use Umbrellio\TableSync\Integration\Laravel\Publishers\QueuePublisher;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageDataRetriever;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\ConflictResolvers\ByTargetKeysResolver;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\ConflictResolvers\ConflictConditionResolverContract;
use Umbrellio\TableSync\Integration\Laravel\ReceivedMessageHandlers\QueueReceivedMessageHandler;
use Umbrellio\TableSync\Publisher;
use Umbrellio\TableSync\Rabbit\ChannelContainer;
use Umbrellio\TableSync\Rabbit\Config;
use Umbrellio\TableSync\Rabbit\ConnectionContainer;
use Umbrellio\TableSync\Rabbit\Consumer as RabbitConsumer;
use Umbrellio\TableSync\Rabbit\Publisher as RabbitPublisher;

class TableSyncServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ConflictConditionResolverContract::class => ByTargetKeysResolver::class,
    ];
    protected $defer = true;

    public function boot(): void
    {
        $config = __DIR__ . '/config/table_sync.php';

        $this->publishes([
            $config => base_path('config/table_sync.php'),
        ], 'config-table-sync');
    }

    public function configureChannel(): void
    {
        $this->app->bind(ChannelContainer::class, function ($app) {
            $channel = new ChannelContainer($app->make(ConnectionContainer::class));
            $channel->setChannelOption(ConfigRepository::get('table_sync.channel'));
            return $channel;
        });
    }

    public function register(): void
    {
        $publishConfig = ConfigRepository::get('table_sync.publish');

        if ($publishConfig !== null) {
            $this->configurePublish($publishConfig);
        }

        $receiveConfig = ConfigRepository::get('table_sync.receive');

        if ($receiveConfig !== null) {
            $this->configureReceive($receiveConfig);
        }

        $logsConfig = ConfigRepository::get('table_sync.log');

        if ($logsConfig !== null) {
            $this->configureLogs($logsConfig);
        }

        $this->configureConnection();
        $this->configureChannel();
    }

    public function provides(): array
    {
        return [Publisher::class];
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\WorkCommand::class,
                Console\Commands\TerminateCommand::class,
                Console\Commands\RestartCommand::class,
            ]);
        }
    }

    private function configurePublish(array $config): void
    {
        $this->app->singleton(Publisher::class, function ($app) use ($config) {
            if (isset($config['custom_publisher'])) {
                return $app->make($config['custom_publisher']);
            }

            return $app->make(QueuePublisher::class, [
                'publisher' => $app->make(RabbitPublisher::class),
            ]);
        });

        $this->app->bind(Config\PublishMessage::class, function () use ($config) {
            [
                'appId' => $appId,
                'headers' => $headers,
            ] = $config['message'];

            return new Config\PublishMessage($appId, new AMQPTable($headers));
        });

        $this->app->bind(Config\Publisher::class, function () use ($config) {
            [
                'exchangeName' => $exchange,
                'confirmSelect' => $confirm,
            ] = $config['publisher'];

            return new Config\Publisher($exchange, $confirm);
        });
    }

    private function configureReceive(array $config): void
    {
        $this->app->bind(MessageDataRetriever::class, function () use ($config) {
            $config = $config['message_configs'] ?? [];
            return new MessageDataRetriever($config);
        });

        $this->app->bind(Config\Consumer::class, function ($app) use ($config) {
            $queue = $config['queue'] ?? '';
            $handler = $config['custom_received_message_handler'] ?? QueueReceivedMessageHandler::class;
            $microsecondsToSleep = $config['microseconds_to_sleep'];

            return (new Config\Consumer($app->make($handler), $queue))->setMicrosecondsToSleep($microsecondsToSleep);
        });

        $pidPath = $config['pid_path'] ?? storage_path('table_sync.pid');

        $this->app->bind(PidManager::class, function () use ($pidPath) {
            return new PidManager($pidPath);
        });

        $this->registerCommands();
    }

    private function configureLogs(array $config): void
    {
        if (($logManager = $this->app->make('log')) instanceof LogManager) {
            $this->app->singleton('table_sync.logger', function () use ($logManager, $config) {
                return $logManager->channel($config['channel']);
            });
        }

        $this->app
            ->when([RabbitPublisher::class, RabbitConsumer::class])
            ->needs(LoggerInterface::class)
            ->give('table_sync.logger')
        ;
    }

    private function configureConnection(): void
    {
        $this->app->singleton(ConnectionContainer::class, function () {
            [
                'host' => $host,
                'port' => $port,
                'user' => $user,
                'pass' => $pass,
                'vhost' => $vhost,
                'sslOptions' => $sslOptions,
                'options' => $options,
            ] = ConfigRepository::get('table_sync.connection');

            return new ConnectionContainer($host, $port, $user, $pass, $vhost, $sslOptions, $options);
        });
    }
}
