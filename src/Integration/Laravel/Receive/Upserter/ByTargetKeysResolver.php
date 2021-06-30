<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Upserter;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Upserter\ConflictConditionResolverContract;

class ByTargetKeysResolver implements ConflictConditionResolverContract
{
    public function resolver(MessageData $messageData): string
    {
        return '(' . implode(',', $messageData->getTargetKeys()) . ')';
    }
}
