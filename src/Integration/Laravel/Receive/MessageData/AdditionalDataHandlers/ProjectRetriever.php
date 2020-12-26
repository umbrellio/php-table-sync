<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandlers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandler;
use Umbrellio\TableSync\Messages\ReceivedMessage;

final class ProjectRetriever implements AdditionalDataHandler
{
    public function handle(ReceivedMessage $message, array $data): array
    {
        [, $projectId] = explode('.', $message->getAppId());
        return array_merge($data, [
            'project_id' => $projectId,
        ]);
    }
}
