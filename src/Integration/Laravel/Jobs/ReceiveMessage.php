<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Umbrellio\TableSync\Integration\Laravel\Receive\Receiver;
use Umbrellio\TableSync\Messages\ReceivedMessage;

final class ReceiveMessage implements ShouldQueue, ShouldStorePayload
{
    use HeavyJobsEnabledTrait;

    private $message;

    public function __construct(ReceivedMessage $message)
    {
        $this->message = $message;
    }

    public function handle(Receiver $receiver): void
    {
        $receiver->receive($this->message);
    }
}
