<?php

namespace Mapali\CodexDump\Tests\Feature;

use Mapali\CodexDump\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $dir = realpath(__DIR__.'/../../src/Commands');
    $this->fixtureDir = is_string($dir) ? $dir : '';
    $this->outputPath = sys_get_temp_dir().'/codex-dump-output.txt';
    @unlink((string) $this->outputPath);
});

afterEach(function () {
    @unlink((string) $this->outputPath);
});

it('dumps codebase from real src/Commands directory', function () {
    $this->artisan('codex:dump')
        ->expectsQuestion('Enter base directory to scan', $this->fixtureDir)
        ->expectsQuestion('Enter output file path (relative or absolute)', $this->outputPath)
        ->expectsOutput('Scanning files...')
        ->assertExitCode(0);

    expect(file_exists((string) $this->outputPath))->toBeTrue();

    $contents = file_get_contents((string) $this->outputPath);

    expect($contents)->toStartWith('>>> FILE TREE');
    expect($contents)->toMatch('/DumpCodebaseCommand\.php/');
    expect($contents)->toMatch('/>>> .*DumpCodebaseCommand\.php/');
    expect($contents)->toContain('<?php');
});
