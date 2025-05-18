<?php

namespace Mapali\CodexDump\Tests\Fixtures;

use Mapali\CodexDump\Support\HistoryRepository;

final class FakeHistoryRepository implements HistoryRepository
{
    private array $data = [];

    public function load(): array
    {
        return $this->data;
    }

    public function save(string $basePath, string $outputPath): void
    {
        $this->data = [
            'basePath' => $basePath,
            'outputPath' => $outputPath,
        ];
    }

    public function clear(): void
    {
        $this->data = [];
    }
}
