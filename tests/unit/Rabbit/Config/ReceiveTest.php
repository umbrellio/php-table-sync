<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use Umbrellio\TableSync\Rabbit\Config\Consumer as Config;
use Umbrellio\TableSync\ReceivedMessageHandler;
use Umbrellio\TableSync\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReceiveTest extends UnitTestCase
{
    #[Test]
    public function parametersFromConstructor(): void
    {
        $handler = $this->createMock(ReceivedMessageHandler::class);
        $config = new Config($handler, 'queue', 'tag');

        $this->assertSame($handler, $config->handler());
        $this->assertSame('queue', $config->queue());
        $this->assertSame('tag', $config->consumerTag());
    }
}
