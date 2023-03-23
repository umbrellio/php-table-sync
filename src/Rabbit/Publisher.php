<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher as PublisherContract;
use Umbrellio\TableSync\Rabbit\Config\Publisher as Config;
use Umbrellio\TableSync\Rabbit\Exceptions\MaxAttemptsExceeded;

final class Publisher implements PublisherContract
{
    public function __construct(
        private readonly Config $config,
        private readonly ConnectionContainer $connectionContainer,
        private readonly MessageBuilder $messageBuilder,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function publish(PublishMessage $message): void
    {
        $this->tryPublish($message);
    }

    private function tryPublish(PublishMessage $message, int $attempt = 1): void
    {
        try {
            $this->attemptToPublish($message);
        } catch (AMQPRuntimeException | AMQPConnectionClosedException $exception) {
            $this->connectionContainer->reconnect();

            if ($attempt === $this->config->attempts()) {
                throw new MaxAttemptsExceeded("Publisher tried {$attempt} times.");
            }

            $this->tryPublish($message, ++$attempt);
        }
    }

    private function attemptToPublish(PublishMessage $message): void
    {
        $channel = $this->connectionContainer->connection()
            ->channel();

        $confirmSelect = $this->config->confirmSelect();
        if ($confirmSelect) {
            $channel->confirm_select();
        }

        $amqpMessage = $this->messageBuilder->buildForPublishing($message);

        $channel->basic_publish(
            $amqpMessage,
            $this->config->exchangeName(),
            $message->routingKey(),
            $confirmSelect
        );

        $this->logger->info('Message publishing', [
            'direction' => 'publish',
            'body' => $amqpMessage->getBody(),
            'props' => $amqpMessage->get_properties(),
            'exchange' => $this->config->exchangeName(),
            'routing_key' => $message->routingKey(),
        ]);

        if ($confirmSelect) {
            $channel->wait_for_pending_acks_returns();
        }
    }
}
