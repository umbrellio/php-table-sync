<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use Umbrellio\LTree\tests\UnitTestCase;
use Umbrellio\TableSync\Rabbit\Config\Publisher as Config;

class PublishTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parametersFromConstructor(): void
    {
        $config = new Config('exchange', false);

        $this->assertSame('exchange', $config->exchangeName());
        $this->assertFalse($config->confirmSelect());
    }
}
