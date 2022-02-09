<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit;

use PhpAmqpLib\Channel\AMQPChannel;

class ChannelContainer
{
    private $connectionContainer;
    private $channel;
    private $channelOptions = [
        'prefetch_size' => null,
        'prefetch_count' => 1,
        'a_global' => true,
    ];

    public function __construct(ConnectionContainer $connectionContainer)
    {
        $this->connectionContainer = $connectionContainer;
    }

    public function __destruct()
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
    }

    public function setChannelOption($option)
    {
        if (!empty($option['prefetch_size'])) {
            $this->channelOptions['prefetch_size'] = $option['prefetch_size'];
        }
        if (!empty($option['prefetch_count'])) {
            $this->channelOptions['prefetch_count'] = $option['prefetch_count'];
        }
        if (!empty($option['a_global'])) {
            $this->channelOptions['a_global'] = $option['a_global'];
        }
    }

    public function getChannel(): AMQPChannel
    {
        if (!isset($this->channel)) {
            $this->channel = $this->connectionContainer->connection()
                ->channel();
            $this->channel->basic_qos(
                $this->channelOptions['prefetch_size'],
                $this->channelOptions['prefetch_count'],
                $this->channelOptions['a_global']
            );
        }
        return $this->channel;
    }
}
