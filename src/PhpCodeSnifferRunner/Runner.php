<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner;

use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;
use RuntimeException;

use function array_key_exists;
use function escapeshellarg;
use function exec;
use function sprintf;
use function strpos;

class Runner
{
    public function run(string $basePath): Report
    {
        return $this->runPhpCodeSniffer($basePath, false);
    }

    public function runIgnoringAnnotations(string $basePath): Report
    {
        return $this->runPhpCodeSniffer($basePath, true);
    }

    private function runPhpCodeSniffer(string $basePath, bool $ignoreAnnotations): Report
    {
        $cliCommand = sprintf(
            'vendor/bin/phpcs -q --report=json%s --basepath=%s',
            $ignoreAnnotations ? ' --ignore-annotations' : '',
            escapeshellarg($basePath)
        );

        // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
        exec($cliCommand, $output);

        if (!array_key_exists(0, $output)) {
            throw new RuntimeException(sprintf('The CLI command \'%s\' did not produce any output.', $cliCommand));
        }
        if (strpos($output[0], 'ERROR:') === 0) {
            throw new RuntimeException($output[0]);
        }

        return (new ReportDeserializer())->deserializeReport($output[0]);
    }
}
