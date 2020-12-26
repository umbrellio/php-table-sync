<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use Umbrellio\TableSync\Rabbit\Config\Consumer as Config;
use Umbrellio\TableSync\ReceivedMessageHandler;
use Umbrellio\TableSync\Tests\UnitTestCase;

class ReceiveTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parametersFromConstructor(): void
    {
        $handler = $this->createMock(ReceivedMessageHandler::class);
        $config = new Config($handler, 'queue', 'tag');

        $this->assertSame($handler, $config->handler());
        $this->assertSame('queue', $config->queue());
        $this->assertSame('tag', $config->consumerTag());
    }
}
