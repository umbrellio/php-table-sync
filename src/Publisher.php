<?php

declare(strict_types=1);

namespace Umbrellio\TableSync;

use Umbrellio\TableSync\Messages\PublishMessage;

interface Publisher
{
    public function publish(PublishMessage $message): void;
}
