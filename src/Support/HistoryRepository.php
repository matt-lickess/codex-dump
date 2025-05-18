<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

interface HistoryRepository
{
    /**
     * @return array{basePath?: string, outputPath?: string}
     */
    public function load(): array;

    public function save(string $basePath, string $outputPath): void;
}
