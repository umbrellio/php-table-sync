<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Publishers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;

final class EnsureConsistencyPublisher implements Publisher
{
    private $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function publish(PublishMessage $message): void
    {
        if (!$this->isRecordExists($message) && !$message->isDestroyed()) {
            return;
        }

        $this->publisher->publish($message);
    }

    private function isRecordExists(PublishMessage $message): bool
    {
        /** @var Model $model */
        $model = App::make($message->className());

        if (!isset($message->attributes()[$model->getKeyName()])) {
            return false;
        }

        return $model
            ->newQuery()
            ->where($model->getKeyName(), $message->attributes()[$model->getKeyName()])
            ->exists();
    }
}
