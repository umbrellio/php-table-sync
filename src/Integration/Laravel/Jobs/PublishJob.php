<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Umbrellio\TableSync\Messages\PublishMessage;

final class PublishJob implements ShouldQueue, ShouldStorePayload
{
    use HeavyJobsEnabledTrait;
    use Queueable;

    private $publisherClass;
    private $message;

    public function __construct(string $publisherClass, PublishMessage $message)
    {
        $this->queue = Config::get('table_sync.publish_job_queue', '');
        $this->publisherClass = $publisherClass;
        $this->message = $message;
    }

    public function handle(): void
    {
        App::make($this->publisherClass)->publish($this->message);
    }
}
