<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\TableSync\Integration\Laravel\Exceptions\Receive\IncorrectAdditionalDataHandler;
use Umbrellio\TableSync\Integration\Laravel\Exceptions\Receive\IncorrectConfiguration;
use Umbrellio\TableSync\Messages\ReceivedMessage;

class MessageDataRetriever
{
    // todo: config should be object
    public function __construct(
        private readonly array $config
    ) {
    }

    public function retrieve(ReceivedMessage $message): MessageData
    {
        $messageConfig = $this->configForMessage($message);
        [$table, $model] = $this->retrieveTableAndModel($messageConfig);
        $targetKeys = $this->retrieveTargetKeys($messageConfig);
        $data = $this->retrieveData($message, $messageConfig);

        return new MessageData($table, $model, $targetKeys, $data);
    }

    private function configForMessage(ReceivedMessage $message): array
    {
        if (!isset($this->config[$message->getModel()])) {
            throw new IncorrectConfiguration("No configuration for {$message->getModel()}");
        }

        return $this->config[$message->getModel()];
    }

    /** @return array{0: ?string, 1: null|class-string<Model>} */
    private function retrieveTableAndModel(array $messageConfig): array
    {
        $table = $messageConfig['table'] ?? null;
        $model = $messageConfig['model'] ?? null;
        if (!$table && !$model) {
            throw new IncorrectConfiguration('Table or Model configuration required');
        }
        if ($table && $model) {
            throw new IncorrectConfiguration('Table and Model configuration cannot be set simultaneously');
        }
        if ($model && !is_subclass_of($model, Model::class)) {
            throw new IncorrectConfiguration('Model must be subclass of ' . Model::class);
        }

        return [$table, $model];
    }

    private function retrieveTargetKeys(array $messageConfig): array
    {
        if (!isset($messageConfig['target_keys'])) {
            throw new IncorrectConfiguration('Target keys configuration required');
        }

        return $messageConfig['target_keys'];
    }

    private function retrieveData(ReceivedMessage $message, array $messageConfig): array
    {
        $data = $this->wrapAttributes($message->getAttributes());

        if (isset($messageConfig['additional_data_handler'])) {
            $data = $this->applyAdditionalHandler($data, $message, $messageConfig['additional_data_handler']);
        }

        if (isset($messageConfig['override_data'])) {
            $data = $this->overrideData($data, $messageConfig['override_data']);
        }

        return $data;
    }

    private function wrapAttributes(array $attributes): array
    {
        $keys = array_keys($attributes);

        $isArray = collect($keys)
            ->every(function ($key) {
                return is_numeric($key);
            });

        return $isArray ? $attributes : [$attributes];
    }

    private function applyAdditionalHandler(
        array $data,
        ReceivedMessage $message,
        string $additionalDataHandlerClass
    ): array {
        $handler = $this->resolveAdditionalDataHandler($additionalDataHandlerClass);

        return array_map(function (array $item) use ($handler, $message) {
            return $handler->handle($message, $item);
        }, $data);
    }

    private function overrideData(array $data, array $override): array
    {
        return array_map(function (array $item) use ($override) {
            foreach ($override as $from => $to) {
                $item[$to] = $item[$from];
                unset($item[$from]);
            }
            return $item;
        }, $data);
    }

    private function resolveAdditionalDataHandler(string $className): AdditionalDataHandler
    {
        $handler = app($className);
        if (!$handler instanceof AdditionalDataHandler) {
            throw new IncorrectAdditionalDataHandler(
                "Additional data handler must implement AdditionalDataHandler interface ({$className})"
            );
        }

        return $handler;
    }
}
