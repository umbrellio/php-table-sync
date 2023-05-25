<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\Saver;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Traits\StubPublisher;

abstract class SaverTestCase extends LaravelTestCase
{
    use StubPublisher;

    /** @var non-empty-string */
    protected const TARGET = null;
    /** @var class-string<Saver> */
    protected const SAVER = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stubPublisher();
    }

    /**
     * @test
     */
    public function upsert(): void
    {
        $data = $this->makeMessageData(['id'], [
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

        $data->upsert(1000.1);

        $this->assertDatabaseHas(static::TARGET, [
            'id' => 1,
            'name' => 'first_name',
            'some_field' => 'first_field',
            'version' => 1000.1,
        ]);

        $this->assertDatabaseHas(static::TARGET, [
            'id' => 2,
            'name' => 'second_name',
            'some_field' => 'second_field',
            'version' => 1000.1,
        ]);

        $newData = $this->makeMessageData(['id'], [
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
        $newData->upsert(1000.11);
        $this->assertDatabaseHas(static::TARGET, $newRawData);

        $newData = $this->makeMessageData(['id'], [
            [
                'id' => 1,
                'name' => 'some_name',
                'some_field' => 'some_field',
            ],
        ]);

        $newData->upsert(1000.1);
        $this->assertDatabaseHas(static::TARGET, $newRawData);
    }

    /**
     * @test
     */
    public function nothingIfUpsertWithEmptyData(): void
    {
        $data = $this->makeMessageData([], [], 'not_exist_table');

        $this->assertNull($data->upsert(10.1));
    }

    /**
     * @test
     */
    public function destroy(): void
    {
        /** @var TestModel $testModel */
        $testModel = factory(TestModel::class)->create();

        $data = $this->makeMessageData(['id'], [[
            'id' => $testModel->id,
        ]]);

        $data->destroy();

        $this->assertNull($testModel->fresh());
    }

    /**
     * @test
     */
    public function destroyBatch(): void
    {
        $models = factory(TestModel::class, 2)->create();
        $data = $models->map(function (TestModel $model) {
            return [
                'id' => $model->id,
            ];
        })->all();

        $message = $this->makeMessageData(['id'], $data);

        $message->destroy();

        foreach ($models as $model) {
            $this->assertNull($model->fresh());
        }
    }

    /**
     * @test
     */
    public function destroyWithSomeAttributes(): void
    {
        /** @var TestModel $destroyedModel */
        $destroyedModel = factory(TestModel::class)->create([
            'name' => 'test',
        ]);
        $model = factory(TestModel::class)->create([
            'name' => 'test',
        ]);

        $message = $this->makeMessageData(['id'], [[
            'id' => $destroyedModel->id,
            'name' => 'test',
        ]]);

        $message->destroy();

        $this->assertNull($destroyedModel->fresh());
        $this->assertNotNull($model->fresh());
    }

    /**
     * @test
     */
    public function nothingIfDestroyWithEmptyData(): void
    {
        $data = $this->makeMessageData([], [], 'not_exist_table');

        $this->assertNull($data->destroy());
    }

    /**
     * @test
     */
    public function destroyByDuplicatedTargetKeys(): void
    {
        $this->dropPrimaryKeyConstraint();
        $models = factory(TestModel::class, 2)->create(['id' => 1]);
        $this->assertDatabaseCount(static::TARGET, 2);
        $models->each(fn (TestModel $model) => $this->assertDatabaseHas(static::TARGET, $model->toArray()));

        $this
            ->makeMessageData(['id'], [['id' => 1]])
            ->destroy();

        $this->assertDatabaseCount(static::TARGET, 0);
    }

    protected function makeMessageData(array $targetKeys, array $data, ?string $target = null): MessageData
    {
        $target = $target ?? static::TARGET;

        return new MessageData($target, App::make(static::SAVER), $targetKeys, $data);
    }

    protected function dropPrimaryKeyConstraint(): void
    {
        DB::statement('alter table test_models drop constraint test_models_pkey;');
    }
}
