<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\_data\Traits;

use phpmock\functions\FixedMicrotimeFunction;
use phpmock\MockBuilder;

/**
 * Should be first for correct mock time.
 *
 * @link https://github.com/php-mock/php-mock#requirements-and-restrictions
 */
trait MicrotimeFunctionMockTrait
{
    private $mockedMicrotime = 1540375350.8454;
    private $enabledMicrotimeMockBuilder = false;
    private $microtimeMockBuilder;

    private function enableMockMicrotime(): self
    {
        if ($this->enabledMicrotimeMockBuilder === false) {
            $this->microtimeMockBuilder->enable();
            $this->enabledMicrotimeMockBuilder = true;
        }

        return $this;
    }

    private function mockMicrotime(): self
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Umbrellio\TableSync\Rabbit')
            ->setName('microtime')
            ->setFunctionProvider(new FixedMicrotimeFunction($this->mockedMicrotime));

        $this->microtimeMockBuilder = $builder->build();

        return $this;
    }

    private function disableMockMicrotime(): self
    {
        if ($this->enabledMicrotimeMockBuilder === true) {
            $this->microtimeMockBuilder->disable();
            $this->enabledMicrotimeMockBuilder = false;
        }

        return $this;
    }
}
