<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use PhpAmqpLib\Wire\AMQPTable;
use Umbrellio\TableSync\Rabbit\Config\PublishMessage as Config;
use Umbrellio\TableSync\Tests\UnitTestCase;

class MessageTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parametersFromConstructor(): void
    {
        $amqpTable = new AMQPTable([]);
        $config = new Config('appId', $amqpTable);

        $this->assertSame('appId', $config->appId());
        $this->assertSame($amqpTable, $config->headers());
    }
}
