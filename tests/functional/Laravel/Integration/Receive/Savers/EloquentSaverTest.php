<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers;

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
}
