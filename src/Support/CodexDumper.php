<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

class CodexDumper
{
    private string $basePath;

    private string $outputPath;

    /** @var string[] */
    private array $ignoreDirs;

    /** @var string[] */
    private array $extensions;

    private int $maxTokens;

    /** @var callable|null */
    private $progressCallback;

    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     */
    public function __construct(
        string $basePath,
        string $outputPath,
        array $ignoreDirs,
        array $extensions,
        int $maxTokens,
        ?callable $progressCallback = null
    ) {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->outputPath = $outputPath;
        $this->ignoreDirs = $ignoreDirs;
        $this->extensions = $extensions;
        $this->maxTokens = $maxTokens;
        $this->progressCallback = $progressCallback;
    }

    /**
     * @return array{file_count: int, estimated_tokens: int, file_tree: string}
     */
    public function run(): array // returns summary
    {
        $estimatedTokens = 0;
        $entries = [];
        $fileCount = 0;

        $filePaths = FileOpsHelper::getOrderedFilePaths($this->basePath, $this->ignoreDirs, $this->extensions);

        foreach ($filePaths as $filePath) {
            $relativePath = ltrim(str_replace($this->basePath, '', $filePath), DIRECTORY_SEPARATOR);

            $header = ">>> {$relativePath}\n";
            $contents = file_get_contents($filePath);
            $entry = $header.$contents."\n\n";

            $entries[] = $entry;
            $estimatedTokens += self::estimateTokens($entry);
            $fileCount++;

            if ($this->progressCallback) {
                call_user_func($this->progressCallback);
            }
        }

        if ($fileCount === 0) {
            throw new \RuntimeException('Export aborted: No matching files found in the given directory.');
        }

        if ($estimatedTokens > $this->getMaxTokens()) {
            throw new \RuntimeException(
                "Export aborted: Estimated token count $estimatedTokens exceeds the limit of {$this->getMaxTokens()}."
            );
        }

        $fileTree = self::getFileTree($this->basePath, $this->ignoreDirs, $this->extensions);
        array_unshift($entries, ">>> FILE TREE\n$fileTree\n\n\n");

        $tmpPath = $this->outputPath.'.tmp';
        $writer = fopen($tmpPath, 'w');
        if ($writer === false) {
            throw new \RuntimeException("Failed to open file for writing: {$tmpPath}");
        }
        foreach ($entries as $entry) {
            fwrite($writer, $entry);
        }
        fclose($writer);

        rename($tmpPath, $this->outputPath);

        return [
            'file_count' => $fileCount,
            'estimated_tokens' => $estimatedTokens,
            'file_tree' => $fileTree,
        ];
    }

    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     */
    public static function getFileTree(string $basePath, array $ignoreDirs, array $extensions): string
    {
        return FileTreeHelper::generateTree($basePath, $ignoreDirs, $extensions);
    }

    private static function estimateTokens(string $text): int
    {
        // Approximate: 1 token per 4 characters
        return (int) ceil(strlen($text) / 4);
    }

    private function getMaxTokens(): int
    {
        return $this->maxTokens;
    }
}
