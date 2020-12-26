<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration;

use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\SoftTestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModelWithExceptedFields;
use Umbrellio\TableSync\Tests\functional\Laravel\Traits\SpyPublisher;

class TableSyncObserverTest extends LaravelTestCase
{
    use SpyPublisher;

    private $spyPublisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spyPublisher = $this->makeSpyPublsiher();

        $this->app->bind(Publisher::class, function () {
            return $this->spyPublisher;
        });
    }

    /**
     * @test
     */
    public function createdMessagePublished(): void
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)->create([
            'name' => 'test_name',
            'some_field' => 'some_test_field',
        ]);
        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertTrue($message->isCreated());
        $this->assertSame(TestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
            'name' => 'test_name',
            'some_field' => 'some_test_field',
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function updatedMessagePublished(): void
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)->create();
        $model->update([
            'name' => 'new_name',
            'some_field' => 'new_field',
        ]);

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertFalse($message->isCreated());
        $this->assertFalse($message->isDestroyed());
        $this->assertSame(TestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
            'name' => 'new_name',
            'some_field' => 'new_field',
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function deletedMessagePublished(): void
    {
        /** @var TestModel $model */
        $model = factory(TestModel::class)->create();
        $model->delete();

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertTrue($message->isDestroyed());
        $this->assertSame(TestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function softDeletedMessagePublished(): void
    {
        /** @var SoftTestModel $model */
        $model = factory(SoftTestModel::class)->create([
            'name' => 'test_name',
        ]);
        $model->delete();

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertFalse($message->isDestroyed());
        $this->assertSame(SoftTestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
            'name' => 'test_name',
            'deleted_at' => $model->deleted_at->toDateTimeString(),
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function forceDeletedMessagePublished(): void
    {
        /** @var SoftTestModel $model */
        $model = factory(SoftTestModel::class)->create();
        $model->forceDelete();

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertTrue($message->isDestroyed());
        $this->assertSame(SoftTestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function restoredMessagePublished(): void
    {
        /** @var SoftTestModel $model */
        $model = factory(SoftTestModel::class)->create([
            'name' => 'test_name',
        ]);
        $model->delete();
        $model->restore();

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertFalse($message->isDestroyed());
        $this->assertFalse($message->isCreated());
        $this->assertSame(SoftTestModel::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
            'name' => 'test_name',
            'deleted_at' => null,
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function modifySyncableAttributes(): void
    {
        /** @var TestModelWithExceptedFields $model */
        $model = factory(TestModelWithExceptedFields::class)->create();
        $model->update([
            'name' => 'new_name',
        ]);

        /** @var PublishMessage $message */
        $message = $this->spyPublisher->messages->last();
        $this->assertFalse($message->isDestroyed());
        $this->assertSame(TestModelWithExceptedFields::class, $message->className());
        $this->assertSame([
            'id' => $model->id,
            'name' => 'new_name',
        ], $message->attributes());
    }

    /**
     * @test
     */
    public function notPublishIfExistsButNotFresh(): void
    {
        $this->spyPublisher->shouldSkip = true;
        /** @var TestModelWithExceptedFields $model */
        $model = factory(TestModelWithExceptedFields::class)->create();

        $modelToDelete = clone $model;
        $modelToDelete->delete();

        $this->spyPublisher->shouldSkip = false;

        $model->update([
            'some_field' => 'new_field',
        ]);

        $this->assertEmpty($this->spyPublisher->messages);
    }

    /**
     * @test
     */
    public function mustNotObserveIfModelTableSyncDisabled(): void
    {
        TestModel::$isTableSyncEnabled = false;

        factory(TestModel::class)->create();
        $this->assertEmpty($this->spyPublisher->messages);
    }
}
