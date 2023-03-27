<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\Unit\Logging;

use DateTimeImmutable;
use InfluxDB\Point;
use Monolog\Level;
use Monolog\LogRecord;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Monolog\Formatter\InfluxDBFormatter;
use Umbrellio\TableSync\Monolog\Formatter\JsonTableSyncFormatter;
use Umbrellio\TableSync\Monolog\Formatter\LineTableSyncFormatter;
use Umbrellio\TableSync\Monolog\Formatter\TableSyncFormatter;
use Umbrellio\TableSync\Rabbit\Config\PublishMessage as Config;
use Umbrellio\TableSync\Rabbit\MessageBuilder;
use Umbrellio\TableSync\Tests\_data\Traits\MicrotimeFunctionMockTrait;
use Umbrellio\TableSync\Tests\UnitTestCase;

class FormatterTest extends UnitTestCase
{
    use MicrotimeFunctionMockTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockMicrotime();
    }

    /**
     * @test
     */
    public function tableSyncFormat(): void
    {
        $this->enableMockMicrotime();

        $tableSyncFormatter = new TableSyncFormatter();
        $format = $tableSyncFormatter->format($this->getDummyRecord());
        $this->assertIsArray($format);
        $this->assertIsArray($format['attributes']);

        $record = new LogRecord(new DateTimeImmutable(), '', Level::Debug, '');
        $format = $tableSyncFormatter->format($record);
        $this->assertSame([
            'datetime',
            'message',
            'direction',
            'routing',
            'model',
            'event',
            'attributes',
            'exception',
        ], array_keys($format));

        $this->disableMockMicrotime();
    }

    /**
     * @test
     */
    public function lineTableSyncFormat(): void
    {
        $lineTableSyncFormatter = new LineTableSyncFormatter();
        $format = $lineTableSyncFormatter->format($this->getDummyRecord());
        $this->assertIsString($format);
        $this->assertStringContainsString('message', $format);
    }

    /**
     * @test
     */
    public function influxDbFormatter(): void
    {
        $influxDBFormatter = new InfluxDBFormatter('measurement', 1);
        $format = $influxDBFormatter->format($this->getDummyRecord());
        $this->assertIsArray($format);
        $this->assertContainsOnlyInstancesOf(Point::class, $format);
        $this->assertSame(['model', 'event', 'direction'], array_keys($format[0]->getTags()));
    }

    /**
     * @test
     */
    public function jsonTableSyncFormat(): void
    {
        $jsonTableSyncFormatter = new JsonTableSyncFormatter();
        $dateTime = new DateTimeImmutable();
        $format = $jsonTableSyncFormatter->format($this->getDummyRecord($dateTime));
        $expected = '{"datetime":"' . $dateTime->format('Y-m-d\TH:i:s.uP') .
            '","message":"message","direction":"direction","routing":"routing_key",' .
            '"model":"model","event":"update","count":1}' . "\n";
        $this->assertIsString($format);
        $this->assertSame($expected, $format);
    }

    private function getDummyRecord(DateTimeImmutable $dateTime = new DateTimeImmutable()): LogRecord
    {
        $message = new PublishMessage('model', 'event', 'routingKey', [
            'id' => 1,
        ]);
        $amqpMessage = (new MessageBuilder(new Config('appId')))->buildForPublishing($message);
        $context = [
            'direction' => 'direction',
            'routing_key' => 'routing_key',
            'body' => $amqpMessage->getBody(),
        ];

        return new LogRecord($dateTime, '', Level::Debug, 'message', $context);
    }
}
