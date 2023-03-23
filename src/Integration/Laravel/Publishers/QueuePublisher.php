<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Publishers;

use Illuminate\Contracts\Bus\Dispatcher;
use Umbrellio\TableSync\Integration\Laravel\Jobs\PublishJob;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;

final class QueuePublisher implements Publisher
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Dispatcher $dispatcher
    ) {
    }

    public function publish(PublishMessage $message): void
    {
        $this->dispatcher->dispatch(new PublishJob(get_class($this->publisher), $message));
    }
}
