<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use PhpAmqpLib\Wire\AMQPTable;
use Umbrellio\TableSync\Rabbit\Config\PublishMessage as Config;
use Umbrellio\TableSync\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class MessageTest extends UnitTestCase
{
    #[Test]
    public function parametersFromConstructor(): void
    {
        $amqpTable = new AMQPTable([]);
        $config = new Config('appId', $amqpTable);

        $this->assertSame('appId', $config->appId());
        $this->assertSame($amqpTable, $config->headers());
    }
}
