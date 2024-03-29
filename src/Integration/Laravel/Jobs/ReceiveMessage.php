<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Umbrellio\TableSync\Integration\Laravel\Receive\Receiver;
use Umbrellio\TableSync\Messages\ReceivedMessage;

final class ReceiveMessage implements ShouldQueue, ShouldStorePayload
{
    use HeavyJobsEnabledTrait;
    use Queueable;

    public function __construct(
        private readonly ReceivedMessage $message
    ) {
        $this->queue = Config::get('table_sync.receive_job_queue', '');
    }

    public function handle(Receiver $receiver): void
    {
        $receiver->receive($this->message);
    }
}
