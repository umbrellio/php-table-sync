<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Publishers;

use Illuminate\Contracts\Bus\Dispatcher;
use Umbrellio\TableSync\Integration\Laravel\Jobs\PublishJob;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;

final class QueuePublisher implements Publisher
{
    private $publisher;
    private $dispatcher;

    public function __construct(Publisher $publisher, Dispatcher $dispatcher)
    {
        $this->publisher = $publisher;
        $this->dispatcher = $dispatcher;
    }

    public function publish(PublishMessage $message): void
    {
        $this->dispatcher->dispatch(new PublishJob(get_class($this->publisher), $message));
    }
}
