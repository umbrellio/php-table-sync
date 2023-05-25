<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers\ConflictResolvers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

class ByTargetKeysResolver implements ConflictConditionResolverContract
{
    public function resolve(MessageData $messageData): string
    {
        return '(' . implode(',', $messageData->getTargetKeys()) . ')';
    }
}
