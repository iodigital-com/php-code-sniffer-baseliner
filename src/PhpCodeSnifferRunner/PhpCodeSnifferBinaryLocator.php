<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner;

use JsonException;

use function array_key_exists;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function preg_match;
use function rtrim;
use function str_replace;

use const JSON_THROW_ON_ERROR;

/**
 * Locates the phpcs binary of a project, following the way Composer resolves its `bin-dir`: the COMPOSER_BIN_DIR
 * environment variable wins over the `config.bin-dir` of the project, which wins over the one in the global Composer
 * config, which wins over the default `{$vendor-dir}/bin`.
 */
class PhpCodeSnifferBinaryLocator
{
    private const DEFAULT_VENDOR_DIR = 'vendor';
    private const DEFAULT_BIN_DIR = '{$vendor-dir}/bin';

    /**
     * @param array<string, string> $environment
     */
    public function __construct(private readonly array $environment)
    {
    }

    public function locate(string $projectDirectory): string
    {
        $projectConfig = $this->readConfig($projectDirectory . '/composer.json');
        $globalConfig = $this->readConfig($this->getGlobalComposerHome() . '/composer.json');

        $vendorDirectory = $this->resolveSetting('COMPOSER_VENDOR_DIR', 'vendor-dir', $projectConfig, $globalConfig);
        $binDirectory = $this->resolveSetting('COMPOSER_BIN_DIR', 'bin-dir', $projectConfig, $globalConfig);

        $binDirectory = str_replace(
            '{$vendor-dir}',
            $vendorDirectory !== null ? $vendorDirectory : self::DEFAULT_VENDOR_DIR,
            $binDirectory !== null ? $binDirectory : self::DEFAULT_BIN_DIR
        );

        if (preg_match('~^(/|[A-Za-z]:[\\\\/])~', $binDirectory) !== 1) {
            $binDirectory = $projectDirectory . '/' . $binDirectory;
        }

        return rtrim($binDirectory, '/\\') . '/phpcs';
    }

    /**
     * @param array<mixed> $projectConfig
     * @param array<mixed> $globalConfig
     */
    private function resolveSetting(
        string $environmentVariable,
        string $configKey,
        array $projectConfig,
        array $globalConfig
    ): ?string {
        foreach ([$this->environment, $projectConfig, $globalConfig] as $index => $source) {
            $key = $index === 0 ? $environmentVariable : $configKey;
            if (array_key_exists($key, $source) && is_string($source[$key]) && $source[$key] !== '') {
                return $source[$key];
            }
        }

        return null;
    }

    private function getGlobalComposerHome(): string
    {
        if ($this->hasValue('COMPOSER_HOME')) {
            return $this->environment['COMPOSER_HOME'];
        }
        if ($this->hasValue('XDG_CONFIG_HOME')) {
            return $this->environment['XDG_CONFIG_HOME'] . '/composer';
        }
        if (!$this->hasValue('HOME')) {
            return '';
        }

        $legacyHome = $this->environment['HOME'] . '/.composer';

        return is_file($legacyHome . '/composer.json') ? $legacyHome : $this->environment['HOME'] . '/.config/composer';
    }

    private function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->environment) && $this->environment[$name] !== '';
    }

    /**
     * @return array<mixed>
     */
    private function readConfig(string $composerJsonPath): array
    {
        if (!is_file($composerJsonPath)) {
            return [];
        }

        $contents = file_get_contents($composerJsonPath);
        if ($contents === false) {
            return [];
        }

        try {
            $composerJson = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($composerJson) || !array_key_exists('config', $composerJson)) {
            return [];
        }

        return is_array($composerJson['config']) ? $composerJson['config'] : [];
    }
}
