<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandlers\ProjectRetriever;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageDataRetriever;
use Umbrellio\TableSync\Messages\ReceivedMessage;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;

class MessageDataRetrieverTest extends LaravelTestCase
{
    /**
     * @test
     */
    public function exceptionIfNoConfig(): void
    {
        $retriever = new MessageDataRetriever([]);

        $this->expectExceptionMessage('No configuration for test_model');

        $message = $this->makeMessage();
        $retriever->retrieve($message);
    }

    /**
     * @test
     */
    public function exceptionIfNoTable(): void
    {
        $retriever = new MessageDataRetriever([
            'test_model' => [],
        ]);

        $this->expectExceptionMessage('Table configuration required');

        $message = $this->makeMessage();
        $retriever->retrieve($message);
    }

    /**
     * @test
     */
    public function exceptionIfNoTargetKeys(): void
    {
        $retriever = new MessageDataRetriever([
            'test_model' => [
                'table' => 'test_models',
            ],
        ]);

        $this->expectExceptionMessage('Target keys configuration required');

        $message = $this->makeMessage();
        $retriever->retrieve($message);
    }

    /**
     * @test
     */
    public function retrieve(): void
    {
        $retriever = new MessageDataRetriever([
            'test_model' => [
                'table' => 'test_models',
                'target_keys' => ['id'],
            ],
        ]);

        $message = $this->makeMessage([
            'attributes' => [
                [
                    'id' => 1,
                    'some' => 'data',
                ],
            ],
        ]);
        $data = $retriever->retrieve($message);

        $this->assertSame('test_models', $data->getTable());
        $this->assertSame(['id'], $data->getTargetKeys());
        $this->assertSame([
            [
                'id' => 1,
                'some' => 'data',
            ],
        ], $data->getData());
    }

    /**
     * @test
     */
    public function additionalDataHandler(): void
    {
        $retriever = new MessageDataRetriever([
            'test_model' => [
                'table' => 'test_models',
                'target_keys' => ['id'],
                'additional_data_handler' => ProjectRetriever::class,
            ],
        ]);

        $message = $this->makeMessage([
            'attributes' => [
                [
                    'id' => 1,
                    'some' => 'data',
                ],
            ],
        ]);
        $data = $retriever->retrieve($message);

        $this->assertSame([
            [
                'id' => 1,
                'some' => 'data',
                'project_id' => 'test_project',
            ],
        ], $data->getData());
    }

    /**
     * @test
     */
    public function overrideData(): void
    {
        $retriever = new MessageDataRetriever([
            'test_model' => [
                'table' => 'test_models',
                'target_keys' => ['id'],
                'override_data' => [
                    'id' => 'external_id',
                ],
                'additional_data_handler' => ProjectRetriever::class,
            ],
        ]);

        $message = $this->makeMessage([
            'attributes' => [
                [
                    'id' => 1,
                    'some' => 'data',
                ],
            ],
        ]);
        $data = $retriever->retrieve($message);

        $this->assertSame([
            [
                'some' => 'data',
                'project_id' => 'test_project',
                'external_id' => 1,
            ],
        ], $data->getData());
    }


    private function makeMessage(array $override = []): ReceivedMessage
    {
        $default = [
            'event' => 'test_event',
            'model' => 'test_model',
            'attributes' => [
                'name' => 'test_name',
            ],
            'version' => 100.1,
            'metadata' => [
                'created' => false,
            ],
            'appid' => 'group.test_project',
        ];

        $config = array_merge($default, $override);

        return new ReceivedMessage(
            $config['event'],
            $config['model'],
            $config['attributes'],
            $config['version'],
            $config['metadata'],
            $config['appid']
        );
    }
}
