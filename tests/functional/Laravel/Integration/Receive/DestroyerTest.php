<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive;

use Illuminate\Support\Facades\App;
use Umbrellio\TableSync\Integration\Laravel\Receive\Destroyer;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\QuerySaver;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Traits\StubPublisher;

class DestroyerTest extends LaravelTestCase
{
    use StubPublisher;

    /**
     * @var Destroyer
     */
    private $destroyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destroyer = new Destroyer();

        $this->stubPublisher();
    }

    /**
     * @test
     */
    public function destroy(): void
    {
        /** @var TestModel $testModel */
        $testModel = factory(TestModel::class)->create();

        $data = new MessageData($testModel->getTable(), App::make(QuerySaver::class), ['id'], [[
            'id' => $testModel->id,
        ]]);

        $this->destroyer->destroy($data);

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

        $message = new MessageData($models->first()->getTable(), App::make(QuerySaver::class), ['id'], $data);

        $this->destroyer->destroy($message);

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

        $message = new MessageData($destroyedModel->getTable(), App::make(QuerySaver::class), ['id'], [[
            'id' => $destroyedModel->id,
            'name' => 'test',
        ]]);

        $this->destroyer->destroy($message);

        $this->assertNull($destroyedModel->fresh());
        $this->assertNotNull($model->fresh());
    }

    /**
     * @test
     */
    public function nothingIfDataEmpty(): void
    {
        $data = new MessageData('not_exist_table', App::make(QuerySaver::class), [], []);

        $this->assertNull($this->destroyer->destroy($data));
    }
}
