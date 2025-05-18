<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

/**
 * File system and ignore logic.
 */
final class FileOpsHelper
{
    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     */
    public static function countFiles(string $basePath, array $ignoreDirs, array $extensions): int
    {
        $count = 0;
        foreach (self::getOrderedFilePaths($basePath, $ignoreDirs, $extensions) as $file) {
            $count++;
        }

        return $count;
    }

    /**
     * @param  string[]  $ignoreDirs
     */
    public static function isInIgnoredDir(string $filePath, array $ignoreDirs): bool
    {
        $parts = explode(DIRECTORY_SEPARATOR, $filePath);
        foreach ($parts as $segment) {
            if (in_array($segment, $ignoreDirs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     * @return string[]
     */
    public static function getOrderedFilePaths(
        string $basePath,
        array $ignoreDirs,
        array $extensions
    ): array {
        $paths = [];
        self::collectFilePaths($basePath, $ignoreDirs, $extensions, $paths);

        return $paths;
    }

    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     * @param  string[]  $paths
     */
    private static function collectFilePaths(
        string $dir,
        array $ignoreDirs,
        array $extensions,
        array &$paths
    ): void {
        $dirIterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $dirs = [];
        $files = [];

        foreach ($dirIterator as $item) {
            if (! $item instanceof \SplFileInfo) {
                continue;
            }
            $realPath = $item->getRealPath();
            if ($realPath === false) {
                continue;
            }
            if (FileOpsHelper::isInIgnoredDir($realPath, $ignoreDirs)) {
                continue;
            }
            if ($item->isDir()) {
                $dirs[] = $item;
            } elseif ($item->isFile() && in_array(strtolower($item->getExtension()), $extensions, true)) {
                $files[] = $item;
            }
        }

        usort($dirs, fn ($a, $b) => strcmp($a->getFilename(), $b->getFilename()));
        usort($files, fn ($a, $b) => strcmp($a->getFilename(), $b->getFilename()));

        /** @var \SplFileInfo $d */
        foreach ($dirs as $d) {
            $realPath = $d->getRealPath();
            if ($realPath !== false) {
                self::collectFilePaths($realPath, $ignoreDirs, $extensions, $paths);
            }
        }
        /** @var \SplFileInfo $f */
        foreach ($files as $f) {
            $realPath = $f->getRealPath();
            if ($realPath !== false) {
                $paths[] = $realPath;
            }
        }
    }
}
