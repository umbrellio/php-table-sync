<?php

declare(strict_types=1);

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandlers\DefaultDataHandler;

return [
    'connection' => [
        'host' => 'host',
        'port' => 'port',
        'user' => 'user',
        'pass' => 'pass',
        'vhost' => '/',
        'sslOptions' => [],
        'options' => [
            'heartbeat' => 60,
            'read_write_timeout' => 360,
        ],
    ],
    'channel' => [
        'prefetch_size' => null,
        'prefetch_count' => 1,
        'a_global' => true,
    ],
    'publish' => [
        'message' => [
            'appId' => 'group_id.app_id',
            'headers' => [],
        ],
        'publisher' => [
            'exchangeName' => 'group_id.app_id.exchange',
            'confirmSelect' => true,
        ],
        /** 'custom_publisher' => 'SomePublisher' */
    ],
    'receive' => [
        'message_configs' => [
            'SomeClass' => [
                'table' => 'some_class_sync',
                'target_keys' => ['external_id', 'some_other_field'],
                'override_data' => [
                    'id' => 'external_id',
                ],
                'additional_data_handler' => DefaultDataHandler::class,
            ],
        ],
        'queue' => '',
        /** 'custom_received_message_handler' => 'SomeHandler' */
        'microseconds_to_sleep' => 1000000,
    ],
    'laravel_heavy_jobs_enabled' => false,
    'log' => [
        'channel' => 'table_sync',
    ],
    'publish_job_queue' => '',
    'receive_job_queue' => '',
];
