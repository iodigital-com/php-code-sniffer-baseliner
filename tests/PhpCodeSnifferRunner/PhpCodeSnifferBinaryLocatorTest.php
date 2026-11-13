<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner;

use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\PhpCodeSnifferBinaryLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(PhpCodeSnifferBinaryLocator::class)]
class PhpCodeSnifferBinaryLocatorTest extends TestCase
{
    private string $projectDirectory;
    private string $composerHome;

    protected function setUp(): void
    {
        $root = sys_get_temp_dir() . '/' . uniqid('phpcs-baseliner-', true);
        $this->projectDirectory = $root . '/project';
        $this->composerHome = $root . '/composer-home';
        mkdir($this->projectDirectory, 0777, true);
        mkdir($this->composerHome, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory(dirname($this->projectDirectory));
    }

    /**
     * @param array<string, string> $environment
     * @param array<string, string> $projectConfig
     * @param array<string, string> $globalConfig
     */
    #[DataProvider('provideConfigurations')]
    public function testLocate(
        string $expectedPath,
        array $environment,
        array $projectConfig,
        array $globalConfig
    ): void {
        $this->writeComposerJson($this->projectDirectory, $projectConfig);
        $this->writeComposerJson($this->composerHome, $globalConfig);
        $environment['COMPOSER_HOME'] = $this->composerHome;

        $locator = new PhpCodeSnifferBinaryLocator($environment);

        // A leading slash marks an absolute path; everything else is relative to the project directory.
        $expectedPath = $expectedPath[0] === '/' ? $expectedPath : $this->projectDirectory . '/' . $expectedPath;
        self::assertSame($expectedPath, $locator->locate($this->projectDirectory));
    }

    /**
     * @return iterable<string, array{string, array<string, string>, array<string, string>, array<string, string>}>
     */
    public static function provideConfigurations(): iterable
    {
        yield 'defaults to vendor/bin' => ['vendor/bin/phpcs', [], [], []];
        yield 'bin-dir of the project' => ['tools/bin/phpcs', [], ['bin-dir' => 'tools/bin'], []];
        yield 'absolute bin-dir' => ['/opt/bin/phpcs', [], ['bin-dir' => '/opt/bin/'], []];
        yield 'bin-dir of the global config' => ['global-bin/phpcs', [], [], ['bin-dir' => 'global-bin']];
        yield 'project config over global config' => [
            'project-bin/phpcs',
            [],
            ['bin-dir' => 'project-bin'],
            ['bin-dir' => 'global-bin'],
        ];
        yield 'COMPOSER_BIN_DIR over all config' => [
            'env-bin/phpcs',
            ['COMPOSER_BIN_DIR' => 'env-bin'],
            ['bin-dir' => 'project-bin'],
            ['bin-dir' => 'global-bin'],
        ];
        yield 'default bin-dir follows vendor-dir' => ['libs/bin/phpcs', [], ['vendor-dir' => 'libs'], []];
        yield 'vendor-dir placeholder' => [
            'libs/tools/phpcs',
            ['COMPOSER_VENDOR_DIR' => 'libs'],
            ['bin-dir' => '{$vendor-dir}/tools'],
            [],
        ];
    }

    #[DataProvider('provideComposerHomes')]
    public function testFindsTheGlobalConfigThroughTheEnvironment(
        string $variable,
        string $variableValue,
        string $configDirectory
    ): void {
        mkdir($this->composerHome . '/' . $configDirectory, 0777, true);
        $this->writeComposerJson($this->composerHome . '/' . $configDirectory, ['bin-dir' => 'global-bin']);

        $locator = new PhpCodeSnifferBinaryLocator([$variable => $this->composerHome . '/' . $variableValue]);

        self::assertSame($this->projectDirectory . '/global-bin/phpcs', $locator->locate($this->projectDirectory));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideComposerHomes(): iterable
    {
        yield 'COMPOSER_HOME' => ['COMPOSER_HOME', 'custom', 'custom'];
        yield 'XDG_CONFIG_HOME' => ['XDG_CONFIG_HOME', 'xdg', 'xdg/composer'];
        yield 'HOME with .composer' => ['HOME', 'home', 'home/.composer'];
        yield 'HOME with .config/composer' => ['HOME', 'home', 'home/.config/composer'];
    }

    public function testIgnoresInvalidComposerJson(): void
    {
        file_put_contents($this->projectDirectory . '/composer.json', '{not json');

        $locator = new PhpCodeSnifferBinaryLocator(['COMPOSER_HOME' => $this->composerHome]);

        self::assertSame($this->projectDirectory . '/vendor/bin/phpcs', $locator->locate($this->projectDirectory));
    }

    /**
     * @param array<string, string> $config
     */
    private function writeComposerJson(string $directory, array $config): void
    {
        file_put_contents($directory . '/composer.json', json_encode(['config' => $config]));
    }

    private function removeDirectory(string $directory): void
    {
        $entries = scandir($directory);
        self::assertNotFalse($entries);
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($directory);
    }
}
