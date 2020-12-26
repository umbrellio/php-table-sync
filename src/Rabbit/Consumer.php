<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use Closure;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class Consumer
{
    private $channelContainer;
    private $messageBuilder;
    private $config;
    private $logger;

    private $working;

    public function __construct(
        ChannelContainer $channelContainer,
        MessageBuilder $messageBuilder,
        Config\Consumer $config,
        LoggerInterface $logger = null
    ) {
        $this->messageBuilder = $messageBuilder;
        $this->channelContainer = $channelContainer;
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
    }

    public function consume(): void
    {
        $channel = $this->channelContainer->getChannel();

        $channel->basic_consume(
            $this->config->queue(),
            $this->config->consumerTag(),
            false,
            false,
            false,
            false,
            Closure::fromCallable([$this, 'handle'])
        );

        $this->working = true;
        while (count($channel->callbacks) && $this->working) {
            usleep($this->config->microsecondsToSleep());
            $channel->wait();
        }
    }

    public function terminate(): void
    {
        $this->working = false;
    }

    private function handle(AMQPMessage $amqpMessage): void
    {
        $message = $this->messageBuilder->buildReceivedMessage($amqpMessage);
        $messageId = $amqpMessage->delivery_info['delivery_tag'];

        try {
            $this->config->handler()->handle($message);
            $this->channelContainer->getChannel()->basic_ack($messageId);
            $this->logger->info("Message #{$messageId} correctly handled", [
                'direction' => 'receive',
                'body' => $amqpMessage->getBody(),
            ]);
        } catch (Throwable $throwable) {
            $this->logger->debug('Cannot handle message', ['exception' => $throwable]);
            $this->channelContainer->getChannel()->basic_nack($messageId, false, true);
        }
    }
}
