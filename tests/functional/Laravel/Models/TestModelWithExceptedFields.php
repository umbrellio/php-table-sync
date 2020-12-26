<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\TableSync\Integration\Laravel\Contracts\SyncableModel;
use Umbrellio\TableSync\Integration\Laravel\TableSyncable;

/**
 * @property int $id
 * @property string $name
 * @property string $some_field
 */
final class TestModelWithExceptedFields extends Model implements SyncableModel
{
    use TableSyncable;

    public $timestamps = false;

    protected $table = 'test_models';
    protected $fillable = ['name', 'some_field'];

    public function getTableSyncableAttributes(): array
    {
        return array_intersect_key($this->getAttributes(), ['name' => null]);
    }

    public function routingKey()
    {
        return 'test_key';
    }
}
