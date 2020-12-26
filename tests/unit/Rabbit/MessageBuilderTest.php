<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\unit\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Umbrellio\LTree\tests\UnitTestCase;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Rabbit\Config\PublishMessage as Config;
use Umbrellio\TableSync\Rabbit\MessageBuilder;
use Umbrellio\TableSync\Tests\_data\Traits\MicrotimeFunctionMockTrait;

class MessageBuilderTest extends UnitTestCase
{
    use MicrotimeFunctionMockTrait;

    /**
     * @var MessageBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new MessageBuilder(new Config('appId'));
        $this->mockMicrotime();
    }

    /**
     * @test
     */
    public function bodyVersionDependsByMicroTime(): void
    {
        $this->enableMockMicrotime();

        $amqpMessage = $this->builder->buildForPublishing(new PublishMessage('class', 'event', 'test_key'));
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertSame($this->mockedMicrotime, $body['version']);

        $this->disableMockMicrotime();
    }

    /**
     * @test
     */
    public function constantAttributes(): void
    {
        $amqpMessage = $this->builder->buildForPublishing(new PublishMessage('class', 'event', 'test_key'));

        $subset = [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'type' => MessageBuilder::EVENT_NAME,
        ];
        $actual = $amqpMessage->get_properties();

        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($value, $actual[$key]);
        }
    }

    /**
     * @test
     */
    public function attributesDependsByConfig(): void
    {
        $table = new AMQPTable([]);
        $builder = new MessageBuilder(new Config('appId', $table));
        $amqpMessage = $builder->buildForPublishing(new PublishMessage('class', 'event', 'test_key'));

        $subset = [
            'app_id' => 'appId',
            'application_headers' => $table,
        ];
        $actual = $amqpMessage->get_properties();

        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($value, $actual[$key]);
        }
    }

    /**
     * @test
     */
    public function bodyEvent(): void
    {
        $amqpMessage = $this->builder->buildForPublishing(new PublishMessage('class', 'event', 'test_key'));
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertSame('update', $body['event']);

        $amqpMessage = $this->builder->buildForPublishing(
            new PublishMessage('class', PublishMessage::EVENT_DESTROYED, 'test_key')
        );
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertSame('destroy', $body['event']);
    }

    /**
     * @test
     */
    public function bodyMeta(): void
    {
        $amqpMessage = $this->builder->buildForPublishing(new PublishMessage('class', 'event', 'test_key'));
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertFalse($body['metadata']['created']);

        $amqpMessage = $this->builder->buildForPublishing(
            new PublishMessage('class', PublishMessage::EVENT_CREATED, 'test_key')
        );
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertTrue($body['metadata']['created']);
    }

    /**
     * @test
     */
    public function bodyAttributesByMessage(): void
    {
        $amqpMessage = $this->builder->buildForPublishing(
            new PublishMessage('class', 'event', 'test_key', ['foo' => 'bar'])
        );
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertSame('class', $body['model']);
        $this->assertSame(['foo' => 'bar'], $body['attributes']);
    }

    /**
     * @test
     */
    public function bodyKeys(): void
    {
        $amqpMessage = $this->builder->buildForPublishing(
            new PublishMessage('class', 'event', 'test_key', ['foo' => 'bar'])
        );
        $body = $this->decodedBodyFromMessage($amqpMessage);

        $this->assertSame(['event', 'model', 'attributes', 'version', 'metadata'], array_keys($body));
    }

    private function decodedBodyFromMessage(AMQPMessage $message): array
    {
        return json_decode($message->getBody(), true);
    }
}
