<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Traits;

use Umbrellio\TableSync\Publisher;

trait StubPublisher
{
    protected function stubPublisher(): void
    {
        $this->app->bind(Publisher::class, function () {
            return $this->createMock(Publisher::class);
        });
    }
}
