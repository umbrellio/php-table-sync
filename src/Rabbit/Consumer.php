<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use Closure;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Consumer
{
    private bool $working;

    public function __construct(
        private readonly ChannelContainer $channelContainer,
        private readonly MessageBuilder $messageBuilder,
        private readonly Config\Consumer $config,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
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

        $this->config->handler()
            ->handle($message);
        $this->channelContainer->getChannel()
            ->basic_ack($messageId);
        $this->logger->info("Message #{$messageId} correctly handled", [
            'direction' => 'receive',
            'body' => $amqpMessage->getBody(),
        ]);
    }
}
