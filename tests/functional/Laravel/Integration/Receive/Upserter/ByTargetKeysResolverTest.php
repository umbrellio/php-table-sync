<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive\Upserter;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Upserter\ByTargetKeysResolver;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;

class ByTargetKeysResolverTest extends LaravelTestCase
{
    /**
     * @test
     */
    public function correctConditionResolved(): void
    {
        $resolver = new ByTargetKeysResolver();
        $messageData = new MessageData('test_models', null, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'new_name',
            ],
        ]);

        $expectedCondition = '(id,name)';
        $this->assertSame($expectedCondition, $resolver->resolve($messageData));
    }
}
