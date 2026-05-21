<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit\Config;

use Umbrellio\TableSync\Rabbit\Config\Publisher as Config;
use Umbrellio\TableSync\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class PublishTest extends UnitTestCase
{
    #[Test]
    public function parametersFromConstructor(): void
    {
        $config = new Config('exchange', false);

        $this->assertSame('exchange', $config->exchangeName());
        $this->assertFalse($config->confirmSelect());
    }
}
