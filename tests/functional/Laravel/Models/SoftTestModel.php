<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Umbrellio\TableSync\Integration\Laravel\Contracts\SyncableModel;
use Umbrellio\TableSync\Integration\Laravel\TableSyncable;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $deleted_at
 */
final class SoftTestModel extends Model implements SyncableModel
{
    use TableSyncable;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['name'];

    public function routingKey()
    {
        return 'test_key';
    }
}
