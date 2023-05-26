<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers;

use Illuminate\Support\Facades\Config;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\EloquentSaver;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;

class EloquentSaverTest extends SaverTestCase
{
    protected const TARGET = TestModel::class;
    protected const SAVER = EloquentSaver::class;

    /**
     * @test
     */
    public function upsertByDuplicatedTargetKeys(): void
    {
        $this->dropPrimaryKeyConstraint();
        $models = factory(TestModel::class, 2)->create(['id' => 1]);
        $this->assertDatabaseCount(static::TARGET, 2);
        $models->each(fn (TestModel $model) => $this->assertDatabaseHas(static::TARGET, $model->toArray()));

        $name = 'new_name';
        $someField = 'new_some_field';
        $this
            ->makeMessageData(['id'], [
                [
                    'id' => 1,
                    'name' => $name,
                    'some_field' => $someField,
                ],
            ])
            ->upsert(1000.1);

        $models
            ->each(fn (TestModel $model) => $this->assertDatabaseMissing(static::TARGET, $model->toArray()))
            ->each(fn (TestModel $model) => $model->refresh())
            ->each(function (TestModel $model) use ($name, $someField) {
                $this->assertSame($name, $model->name);
                $this->assertSame($someField, $model->some_field);
            });
    }

    /**
     * @test
     */
    public function testUpsertWithChunk(): void
    {
        $this->dropPrimaryKeyConstraint();
        $this->setChunkSize(1);
        $models = factory(TestModel::class, 3)->create(['id' => 1]);
        $this->assertDatabaseCount(static::TARGET, 3);
        $models->each(fn (TestModel $model) => $this->assertDatabaseHas(static::TARGET, $model->toArray()));

        $item1 = [
            'id' => 1,
            'name' => 'name_1',
            'some_field' => 'some_field_1',
        ];
        $item2 = [
            'id' => 1,
            'name' => 'name_2',
            'some_field' => 'some_field_2',
        ];
        $item3 = [
            'id' => 1,
            'name' => 'name_3',
            'some_field' => 'some_field_3',
        ];
        $data = [$item1, $item2, $item3];
        $this
            ->makeMessageData(['id'], $data)
            ->upsert(1000.1);

        $models->each(fn (TestModel $model) => $this->assertDatabaseMissing(static::TARGET, $model->toArray()));
        $this->assertDatabaseHas(static::TARGET, $item1);
        $this->assertDatabaseMissing(static::TARGET, $item2);
        $this->assertDatabaseMissing(static::TARGET, $item3);
    }

    /**
     * @test
     */
    public function testDestroyWithChunk(): void
    {
        $this->dropPrimaryKeyConstraint();
        $this->setChunkSize(2);
        $models = factory(TestModel::class, 3)->create(['id' => 1]);
        $this->assertDatabaseCount(static::TARGET, 3);
        $models->each(fn (TestModel $model) => $this->assertDatabaseHas(static::TARGET, $model->toArray()));

        $this
            ->makeMessageData(['id'], [['id' => 1]])
            ->destroy();

        $this->assertDatabaseCount(static::TARGET, 0);
    }

    private function setChunkSize(int $size): void
    {
        Config::set('table_sync.receive.eloquent_chunk_size', $size);
    }
}
