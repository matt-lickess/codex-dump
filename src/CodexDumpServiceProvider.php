<?php

declare(strict_types=1);

namespace Mapali\CodexDump;

use Illuminate\Support\ServiceProvider;
use Mapali\CodexDump\Commands\DumpCodebaseCommand;
use Mapali\CodexDump\Support\FileHistoryRepository;
use Mapali\CodexDump\Support\HistoryRepository;

class CodexDumpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(
            HistoryRepository::class,
            FileHistoryRepository::class
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/codex-dump.php' => config_path('codex-dump.php'),
            ], 'config');

            $this->commands([
                DumpCodebaseCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/codex-dump.php', 'codex-dump'
        );
    }
}
