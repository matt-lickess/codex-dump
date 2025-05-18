<?php

return [
    'ignore_dirs' => [
        'vendor',
        'node_modules',
        'storage',
        '.git',
    ],

    'extensions' => [
        'php',
    ],

    'default_output' => base_path('codex_dump.txt'),
    'max_tokens' => 10000,
];
