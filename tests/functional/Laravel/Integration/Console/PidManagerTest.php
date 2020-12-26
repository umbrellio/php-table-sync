<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Console;

use org\bovigo\vfs\vfsStream;
use Umbrellio\TableSync\Integration\Laravel\Console\PidManager;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;

class PidManagerTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        vfsStream::setup('testDir');
    }

    /**
     * @test
     */
    public function managing(): void
    {
        $manager = new PidManager(vfsStream::path('test_file'));

        $this->assertFalse($manager->pidExists());

        $manager->writePid(123123);
        $this->assertTrue($manager->pidExists());
        $this->assertSame(123123, $manager->readPid());

        $manager->removePid();
        $this->assertFalse($manager->pidExists());
    }
}
