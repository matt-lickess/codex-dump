<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

/**
 * File tree builder.
 */
final class FileTreeHelper
{
    /**
     * @param  string[]  $ignoreDirs
     * @param  string[]  $extensions
     */
    public static function generateTree(
        string $basePath,
        array $ignoreDirs,
        array $extensions,
        string $prefix = '',
        bool $isRoot = true
    ): string {
        $entries = [];

        $dirIterator = new \FilesystemIterator($basePath, \FilesystemIterator::SKIP_DOTS);

        /** @var \SplFileInfo[] $dirs */
        $dirs = [];
        /** @var \SplFileInfo[] $files */
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

        $all = array_merge($dirs, $files);
        $count = count($all);

        foreach ($all as $i => $item) {
            // $item is always \SplFileInfo
            $isLast = $i === $count - 1;
            $linePrefix = $prefix.($isLast ? '└── ' : '├── ');
            $nextPrefix = $prefix.($isLast ? '    ' : '│   ');

            if ($item->isDir()) {
                $subPath = $item->getRealPath();
                if ($subPath !== false) {
                    $subTree = self::generateTree($subPath, $ignoreDirs, $extensions, $nextPrefix, false);
                    if ($subTree !== '') {
                        $entries[] = $linePrefix.$item->getFilename().'/';
                        $entries[] = $subTree;
                    } else {
                        $entries[] = $linePrefix.$item->getFilename().'/';
                    }
                } else {
                    $entries[] = $linePrefix.$item->getFilename().'/';
                }
            } else {
                $entries[] = $linePrefix.$item->getFilename();
            }
        }

        return implode("\n", $entries);
    }
}
