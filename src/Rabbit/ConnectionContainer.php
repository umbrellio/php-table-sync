<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use ErrorException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;

class ConnectionContainer
{
    private mixed $waitBeforeReconnectMicroseconds = 1000000;

    private ?AbstractConnection $connection;

    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $user,
        private readonly string $pass,
        private readonly string $vhost = '/',
        private readonly array $sslOptions = [],
        private readonly array $options = []
    ) {
        $this->waitBeforeReconnectMicroseconds = $options['wait_before_reconnect_microseconds'] ??
            $this->waitBeforeReconnectMicroseconds;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connection(): AbstractConnection
    {
        if ($this->connection === null) {
            $this->reconnect(false);
        }

        return $this->connection;
    }

    public function reconnect(bool $wait = true): void
    {
        $this->close();

        if ($wait) {
            usleep($this->waitBeforeReconnectMicroseconds);
        }

        $this->connection = new AMQPSSLConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->pass,
            $this->vhost,
            $this->sslOptions,
            $this->options
        );
    }

    public function close(): void
    {
        try {
            if ($this->connection === null) {
                return;
            }

            $this->connection->close();
            $this->connection = null;
        } catch (ErrorException $errorException) {
            // @todo Finish later
        }
    }
}
