<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers\ConflictResolvers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

interface ConflictConditionResolverContract
{
    public function resolve(MessageData $messageData): string;
}
