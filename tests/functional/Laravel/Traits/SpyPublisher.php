<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Traits;

use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;

trait SpyPublisher
{
    protected function makeSpyPublisher(): Publisher
    {
        return new class() implements Publisher {
            public $messages;
            public $shouldSkip = false;

            public function __construct()
            {
                $this->messages = collect();
            }

            public function publish(PublishMessage $message): void
            {
                if ($this->shouldSkip) {
                    return;
                }
                $this->messages->push($message);
            }
        };
    }
}
