<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers;

use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\QuerySaver;

class QuerySaverTest extends SaverTestCase
{
    protected const TARGET = 'test_models';
    protected const SAVER = QuerySaver::class;
}
