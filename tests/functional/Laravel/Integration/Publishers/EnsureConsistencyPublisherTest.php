<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Publishers;

use Umbrellio\TableSync\Integration\Laravel\Publishers\EnsureConsistencyPublisher;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Traits\SpyPublisher;

class EnsureConsistencyPublisherTest extends LaravelTestCase
{
    use SpyPublisher;

    /**
     * @var Publisher
     */
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
    public function simplePublish(): void
    {
        $this->spyPublisher->shouldSkip = true;
        $testModel = factory(TestModel::class)->create();
        $this->spyPublisher->shouldSkip = false;

        $publisher = new EnsureConsistencyPublisher($this->spyPublisher);
        $publisher->publish(new PublishMessage(TestModel::class, 'test', 'test_key', ['id' => $testModel->id]));

        $this->assertNotEmpty($this->spyPublisher->messages);
    }

    /**
     * @test
     */
    public function notPublishIfRecordNotExistsAndNotDestroyed(): void
    {
        $publisher = new EnsureConsistencyPublisher($this->spyPublisher);
        $publisher->publish(new PublishMessage(TestModel::class, 'updated', 'test_key', ['id' => 100]));

        $this->assertEmpty($this->spyPublisher->messages);
    }
}
