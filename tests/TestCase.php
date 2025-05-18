<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Tests;

use Mapali\CodexDump\CodexDumpServiceProvider;
use Mapali\CodexDump\Support\HistoryRepository;
use Mapali\CodexDump\Tests\Fixtures\FakeHistoryRepository;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [CodexDumpServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            HistoryRepository::class,
            FakeHistoryRepository::class
        );
    }
}
