<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Savers\ConflictResolvers;

use Illuminate\Support\Facades\App;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\ConflictResolvers\ByTargetKeysResolver;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\QuerySaver;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;

class ByTargetKeysResolverTest extends LaravelTestCase
{
    /**
     * @test
     */
    public function correctConditionResolved(): void
    {
        $resolver = new ByTargetKeysResolver();
        $messageData = new MessageData('test_models', App::make(QuerySaver::class), ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'new_name',
            ],
        ]);

        $expectedCondition = '(id,name)';
        $this->assertSame($expectedCondition, $resolver->resolve($messageData));
    }
}
