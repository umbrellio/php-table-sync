<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Upserter;

use Illuminate\Support\Facades\App;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\QuerySaver;
use Umbrellio\TableSync\Integration\Laravel\Receive\Upserter\Upserter;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;
use Umbrellio\TableSync\Tests\functional\Laravel\Traits\StubPublisher;

class UpserterTest extends LaravelTestCase
{
    use StubPublisher;

    /**
     * @var Upserter
     */
    private $upserter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->upserter = new Upserter();

        $this->stubPublisher();
    }

    /**
     * @test
     */
    public function upsert(): void
    {
        $data = new MessageData('test_models', App::make(QuerySaver::class), ['id'], [
            [
                'id' => 1,
                'name' => 'first_name',
                'some_field' => 'first_field',
            ],
            [
                'id' => 2,
                'name' => 'second_name',
                'some_field' => 'second_field',
            ],
        ]);

        $this->upserter->upsert($data, 1000.1);

        $this->assertDatabaseHas('test_models', [
            'id' => 1,
            'name' => 'first_name',
            'some_field' => 'first_field',
            'version' => 1000.1,
        ]);

        $this->assertDatabaseHas('test_models', [
            'id' => 2,
            'name' => 'second_name',
            'some_field' => 'second_field',
            'version' => 1000.1,
        ]);

        $newData = new MessageData('test_models', App::make(QuerySaver::class), ['id'], [
            [
                'id' => 1,
                'name' => 'new_name',
                'some_field' => 'first_field',
            ],
        ]);

        $newRawData = [
            'id' => 1,
            'name' => 'new_name',
            'some_field' => 'first_field',
            'version' => 1000.11,
        ];
        $this->upserter->upsert($newData, 1000.11);
        $this->assertDatabaseHas('test_models', $newRawData);

        $newData = new MessageData('test_models', App::make(QuerySaver::class), ['id'], [
            [
                'id' => 1,
                'name' => 'some_name',
                'some_field' => 'some_field',
            ],
        ]);

        $this->upserter->upsert($newData, 1000.1);
        $this->assertDatabaseHas('test_models', $newRawData);
    }

    /**
     * @test
     */
    public function nothingIfDataEmpty(): void
    {
        $data = new MessageData('not_exist_table', App::make(QuerySaver::class), [], []);

        $this->assertNull($this->upserter->upsert($data, 10.1));
    }
}
