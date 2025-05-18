<?php

declare(strict_types=1);

namespace Mapali\CodexDump\Support;

/**
 * Pure helpers for project and prompt-relative paths.
 */
final class PathHelper
{
    public static function toProjectDirRelative(string $path, string $projectRoot): string
    {
        $real = realpath($path) ?: $path;
        $rootPath = realpath($projectRoot);
        $root = rtrim($rootPath !== false ? $rootPath : $projectRoot, DIRECTORY_SEPARATOR);

        if ($real === $root) {
            return '.';
        }
        if (strpos($real, $root.DIRECTORY_SEPARATOR) === 0) {
            return ltrim(substr($real, strlen($root)), DIRECTORY_SEPARATOR);
        }

        return $real;
    }

    public static function resolveProjectPath(string $input, string $parentDir): string
    {
        if (strlen($input) && $input[0] === DIRECTORY_SEPARATOR) {
            return $input;
        }
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $input)) {
            return $input;
        }
        if ($input === '.' || $input === './') {
            return rtrim($parentDir, DIRECTORY_SEPARATOR);
        }

        return rtrim($parentDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($input, DIRECTORY_SEPARATOR);
    }

    public static function ensureAbsolutePath(string $path): string
    {
        $real = realpath($path);
        if ($real !== false) {
            return $real;
        }
        if (strlen($path) && $path[0] === DIRECTORY_SEPARATOR) {
            return $path;
        }
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            return $path;
        }

        return getcwd().DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }

    public static function normalizeOutputPathInput(string $outputPathInput): string
    {
        $outputPathInput = trim($outputPathInput);
        $ext = pathinfo($outputPathInput, PATHINFO_EXTENSION);

        if ($ext === '') {
            $outputPathInput .= '.txt';
        } elseif (strtolower($ext) !== 'txt') {
            throw new \InvalidArgumentException('Output file must have a .txt extension.');
        }

        return $outputPathInput;
    }
}
