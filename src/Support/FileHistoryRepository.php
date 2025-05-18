<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

class FileHistoryRepository implements HistoryRepository
{
    public function getHistoryPath(): string
    {
        if (app()->environment() === 'testing') {
            throw new \RuntimeException('Do not use real FileHistoryRepository in testing.');
        }

        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: '';

        return rtrim((string) $home, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.codex-dump.json';
    }

    /**
     * @return array{basePath?: string, outputPath?: string}
     */
    public function load(): array
    {
        $path = $this->getHistoryPath();
        if (is_file($path)) {
            $contents = file_get_contents($path);
            $data = is_string($contents) ? json_decode($contents, true) : null;

            $result = [];
            if (is_array($data)) {
                if (isset($data['basePath']) && is_string($data['basePath'])) {
                    $result['basePath'] = $data['basePath'];
                }
                if (isset($data['outputPath']) && is_string($data['outputPath'])) {
                    $result['outputPath'] = $data['outputPath'];
                }

                return $result;
            }
        }

        return [];
    }

    public function save(string $basePath, string $outputPath): void
    {
        $data = [
            'basePath' => $basePath,
            'outputPath' => $outputPath,
        ];
        file_put_contents($this->getHistoryPath(), json_encode($data));
    }
}
