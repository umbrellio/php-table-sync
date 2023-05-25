<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers;

use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\EloquentSaver;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;

class EloquentSaverTest extends SaverTestCase
{
    protected const TARGET = TestModel::class;
    protected const SAVER = EloquentSaver::class;
}
