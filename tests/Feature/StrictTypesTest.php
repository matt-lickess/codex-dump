<?php

function phpFiles(string $dir): array
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        /** @var SplFileInfo $file */
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

$srcDir = realpath(__DIR__.'/../../src');

it('all PHP files declare strict_types', function () use ($srcDir) {
    $missing = [];

    foreach (phpFiles($srcDir) as $path) {
        $relative = ltrim(str_replace($srcDir, '', $path), DIRECTORY_SEPARATOR);
        $contents = file_get_contents($path);

        if (! preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $contents)) {
            $missing[] = $relative;
        }
    }

    expect($missing)
        ->toBeEmpty("Missing strict_types declaration in the following files:\n".implode("\n", $missing));
});
