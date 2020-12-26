<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Messages;

use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Tests\UnitTestCase;

class PublishMessageTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parametersFromConstructor(): void
    {
        $message = new PublishMessage('class', 'event', 'test_key', [
            'a' => 'b',
        ]);
        $this->assertSame('class', $message->className());
        $this->assertSame([
            'a' => 'b',
        ], $message->attributes());
        $this->assertSame('test_key', $message->routingKey());
    }

    /**
     * @test
     */
    public function detectIfDestroyed(): void
    {
        $message = new PublishMessage('class', 'event', 'test_key');
        $this->assertFalse($message->isDestroyed());

        $message = new PublishMessage('class', PublishMessage::EVENT_DESTROYED, 'test_key');
        $this->assertTrue($message->isDestroyed());
    }

    /**
     * @test
     */
    public function detectIfCreated(): void
    {
        $message = new PublishMessage('class', 'event', 'test_key');
        $this->assertFalse($message->isCreated());

        $message = new PublishMessage('class', PublishMessage::EVENT_CREATED, 'test_key');
        $this->assertTrue($message->isCreated());
    }
}
