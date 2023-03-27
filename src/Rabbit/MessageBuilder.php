<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPArray;
use PhpAmqpLib\Wire\AMQPTable;
use function Safe\json_decode;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Messages\ReceivedMessage;

class MessageBuilder
{
    public const EVENT_NAME = 'table_sync';

    public function __construct(
        private readonly Config\PublishMessage $publishMessageConfig
    ) {
    }

    public function buildReceivedMessage(AMQPMessage $message): ReceivedMessage
    {
        [
            'event' => $event,
            'model' => $model,
            'attributes' => $attributes,
            'version' => $version,
            'metadata' => $metadata,
        ] = json_decode($message->body, true);

        $appId = $message->get('app_id');
        $headers = $message->has('application_headers')
            ? $this->headersToArray($message->get('application_headers'))
            : [];

        return new ReceivedMessage($event, $model, $attributes, $version, $metadata, $appId, $headers);
    }

    public function buildForPublishing(PublishMessage $message): AMQPMessage
    {
        return new AMQPMessage(
            $this->buildBody($message),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'app_id' => $this->publishMessageConfig->appId(),
                'type' => self::EVENT_NAME,
                'application_headers' => $this->publishMessageConfig->headers(),
            ]
        );
    }

    private function headersToArray(AMQPTable $table): array
    {
        $headers = [];
        foreach ($table as $key => $value) {
            if ($value[1] instanceof AMQPArray) {
                $headers[$key] = $value[1]->getNativeData();
            } else {
                $headers[$key] = $value[1];
            }
        }

        return $headers;
    }

    private function buildBody(PublishMessage $message): string
    {
        $event = $message->isDestroyed() ? 'destroy' : 'update';

        $data = [
            'event' => $event,
            'model' => $message->className(),
            'attributes' => $message->attributes(),
            'version' => microtime(true),
            'metadata' => [
                'created' => $message->isCreated(),
            ],
        ];
        return json_encode($data);
    }
}
