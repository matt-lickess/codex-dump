<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Commands;

use Illuminate\Console\Command;
use Mapali\CodexDump\Support\CodexDumper;
use Mapali\CodexDump\Support\FileOpsHelper;
use Mapali\CodexDump\Support\HistoryRepository;
use Mapali\CodexDump\Support\PathHelper;

use function Laravel\Prompts\text;

/**
 * Handles user interaction and orchestration only.
 */
class DumpCodebaseCommand extends Command
{
    protected $signature = 'codex:dump';

    protected $description = 'Export all PHP files in a directory tree into a single .txt for AI ingestion.';

    private HistoryRepository $history;

    public function __construct(HistoryRepository $history)
    {
        parent::__construct();
        $this->history = $history;
    }

    public function handle(): int
    {
        $history = $this->history->load();

        $projectRoot = base_path();

        $defaultBase = $projectRoot;
        if (isset($history['basePath'])) {
            $defaultBase = $history['basePath'];
        }

        $configDefaultOut = config('codex-dump.default_output');
        $defaultOut = $projectRoot.'/codex_dump.txt';
        if (isset($history['outputPath'])) {
            $defaultOut = $history['outputPath'];
        } elseif (is_string($configDefaultOut)) {
            $defaultOut = $configDefaultOut;
        }

        $basePromptDefault = PathHelper::toProjectDirRelative($defaultBase, $projectRoot);
        if ($basePromptDefault === '' || $basePromptDefault === '/') {
            $basePromptDefault = '.';
        }
        $outputPromptDefault = PathHelper::toProjectDirRelative($defaultOut, $projectRoot);

        $basePathInputRaw = text(
            label: 'Enter base directory to scan',
            default: $basePromptDefault,
            required: true,
            validate: fn ($value) => (
                ! is_string($value) ? 'Invalid input.' :
                    ($value === '/' ? 'Refusing to scan root directory.' :
                        (is_dir(PathHelper::resolveProjectPath($value, $projectRoot)) ? null : 'Directory does not exist.'))
            )
        );

        $basePathInput = $basePathInputRaw;

        $outputPathInputRaw = text(
            label: 'Enter output file path (relative or absolute)',
            default: $outputPromptDefault,
            required: true,
            validate: function ($value) {
                if (! is_string($value)) {
                    return 'Invalid input.';
                }
                $value = trim($value);
                $ext = pathinfo($value, PATHINFO_EXTENSION);
                if ($ext !== '' && strtolower($ext) !== 'txt') {
                    return 'Output file must have a .txt extension.';
                }

                return null;
            }
        );

        $outputPathInput = $outputPathInputRaw;
        $outputPathInput = PathHelper::normalizeOutputPathInput($outputPathInput);

        $basePath = PathHelper::resolveProjectPath($basePathInput, $projectRoot);
        $outputPath = PathHelper::resolveProjectPath($outputPathInput, $projectRoot);

        // --- Strict config typing ---
        $ignoreDirs = config('codex-dump.ignore_dirs');
        if (! is_array($ignoreDirs) || array_values($ignoreDirs) !== $ignoreDirs || array_filter($ignoreDirs, 'is_string') !== $ignoreDirs) {
            $ignoreDirs = [];
        }

        $extensions = config('codex-dump.extensions');
        if (! is_array($extensions) || array_values($extensions) !== $extensions || array_filter($extensions, 'is_string') !== $extensions) {
            $extensions = [];
        }

        $maxTokens = config('codex-dump.max_tokens');
        if (! is_int($maxTokens)) {
            $maxTokens = 10000;
        }

        /** @var string[] $ignoreDirs */
        /** @var string[] $extensions */
        /** @var int $maxTokens */
        $totalFiles = FileOpsHelper::countFiles($basePath, $ignoreDirs, $extensions);

        $this->info('Scanning files...');

        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->start();

        try {
            $summary = (new CodexDumper(
                $basePath,
                $outputPath,
                $ignoreDirs,
                $extensions,
                $maxTokens,
                function () use ($progressBar): void {
                    $progressBar->advance();
                }
            ))->run();
        } catch (\RuntimeException $e) {
            $progressBar->finish();
            $this->newLine();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $progressBar->finish();
        $this->newLine();

        $absolutePath = PathHelper::ensureAbsolutePath($outputPath);

        $this->history->save($basePath, $outputPath);

        $this->line($summary['file_tree']);
        $this->info("Export complete: $absolutePath");
        $this->info("Files exported: {$summary['file_count']}");
        $this->info("Estimated tokens: {$summary['estimated_tokens']}");

        return self::SUCCESS;
    }
}
