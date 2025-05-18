# CodexDump

Exports all PHP files in a Laravel project into a single text file for AI ingestion, search, or analysis.

---

## Installation

```bash
composer require mapali/codex-dump --dev
```

---

## Usage

Run the artisan command and follow the prompts:

```bash
php artisan codex:dump
```

You will be prompted to:

- **Enter base directory to scan:**  
  The root folder you want to export files from (default: your Laravel project root).

- **Enter output file path (relative or absolute):**  
  Where the combined export will be written (default: `codex_dump.txt` in your project root).

When finished, you’ll see an "Export complete" message with the output path.

---

## Output Format

The export file contains all matched files in the following format:

```
>>> path/to/File.php
<?php

// File contents...

>>> path/to/AnotherFile.php
<?php

// File contents...
```

---

## Configuration

Publish the config file to customize ignored directories, file extensions, or token limits:

```bash
php artisan vendor:publish --tag=config
```

This creates `config/codex-dump.php`:

```php
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
```

- **ignore_dirs:** Exclude these directories from export.
- **extensions:** File extensions to include.
- **default_output:** Default export file path.
- **max_tokens:** Abort if estimated token count exceeds this value (approximate, 1 token ≈ 4 chars).

---

## Testing

If you contribute, run the test suite:

```bash
./vendor/bin/phpunit
```

---

## Requirements

- Laravel 9 or later (tested with Laravel 10+)
- PHP 8.1 or later

---

## License

MIT

---

## Author

[Mapali / Matt Lickess](https://github.com/mattlickess)

---

**CodexDump** streamlines codebase exports for LLMs and static analysis.  
For issues or feature requests, submit on [GitHub](https://github.com/mapali/codex-dump).
