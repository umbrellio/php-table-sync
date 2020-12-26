<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use ErrorException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;

class ConnectionContainer
{
    private const DEFAULT_WAIT_BEFORE_RECONNECT_MICROSECONDS = 1000000;

    private $host;
    private $port;
    private $user;
    private $pass;
    private $vhost;
    private $sslOptions;
    private $options;
    private $waitBeforeReconnectMicroseconds;

    /**
     * @var AbstractConnection|null
     */
    private $connection;

    public function __construct(
        string $host,
        string $port,
        string $user,
        string $pass,
        string $vhost = '/',
        array $sslOptions = [],
        array $options = []
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->vhost = $vhost;
        $this->sslOptions = $sslOptions;
        $this->options = $options;

        $this->waitBeforeReconnectMicroseconds = $options['wait_before_reconnect_microseconds']
            ?? self::DEFAULT_WAIT_BEFORE_RECONNECT_MICROSECONDS;
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
