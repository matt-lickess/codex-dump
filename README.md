# Codex Dump for Laravel

Codex Dump is a Laravel package designed to consolidate and export your project's PHP source files into a single structured text file. This streamlined output serves as optimized context input for AI-driven code assistance tools like ChatGPT, Codex, or similar systems.

## Features

- **Selective Export:** Define directories and file types to include or exclude.
- **Token Management:** Prevents exceeding AI token limits by providing token estimations and configurable maximum limits.
- **Persistent History:** Remembers previously used directories and file paths for rapid reuse.
- **Interactive CLI:** Offers intuitive prompts for quick and accurate configuration.
- **Automatic File Structure:** Clearly documents the exported file structure for easy AI interpretation.

---

## Installation

Install using Composer:

```bash
composer require mapali/codex-dump
```

Publish the configuration (optional):

```bash
php artisan vendor:publish --provider="Mapali\CodexDump\CodexDumpServiceProvider" --tag="config"
```

---

## Configuration

By default, the package exports all `.php` files, excluding commonly ignored directories (`vendor`, `node_modules`, `.git`, `storage`). Customize these options via `config/codex-dump.php`:

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

- **`ignore_dirs`**: Directories to skip.
- **`extensions`**: File extensions to include.
- **`default_output`**: Default path for the exported text file.
- **`max_tokens`**: Maximum allowed tokens to ensure compatibility with AI models.

---

## Usage

To export your project's codebase, run:

```bash
php artisan codex:dump
```

You'll be interactively prompted for:

- **Base directory:** Directory path to scan (default: current directory).
- **Output file path:** Path for the resulting `.txt` file.

The package will then:

- Calculate the estimated token usage.
- Validate against configured token limits.
- Generate a neatly structured `.txt` file containing your project's files, clearly delineated for optimal AI ingestion.

Example output structure:

```
>>> FILE TREE
├── src/
│   ├── Commands/
│   │   └── DumpCodebaseCommand.php
│   └── Support/
│       └── CodexDumper.php

>>> src/Commands/DumpCodebaseCommand.php
<?php
declare(strict_types=1);

// [file contents here]

>>> src/Support/CodexDumper.php
<?php
declare(strict_types=1);

// [file contents here]
```

---

## Recommended Use

Codex Dump streamlines preparing codebases for AI-assisted development. It is ideal for:

- Providing comprehensive context to AI coding assistants like ChatGPT.
- Quick reference exports for structured AI interactions.
- Facilitating efficient onboarding and understanding of large codebases.

---

## Testing

The package includes comprehensive tests to ensure reliability:

```bash
vendor/bin/phpunit
```

---

## Contributing

Contributions are welcome. Please submit pull requests or issues directly to the GitHub repository.

---

## License

Codex Dump is released under the MIT License.
