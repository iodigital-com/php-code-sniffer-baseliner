<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests;

use IODigital\CodeSnifferBaseliner\Baseline\BaselineFactory;
use IODigital\CodeSnifferBaseliner\BasePathFinder;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;
use IODigital\CodeSnifferBaseliner\Tests\Filesystem\MemoryFilesystem;
use IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\FileReportFactory;
use IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\MessageFactory;
use IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\ReportFactory;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\InstructionCommentCollector;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaselineFinder;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaselineDetector;
use IODigital\CodeSnifferBaseliner\Util\OutputWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UnnecessaryBaselineDetectorTest extends TestCase
{
    /**
     * @var Runner&MockObject
     */
    private Runner&MockObject $runnerMock;
    /**
     * @var OutputWriter&MockObject
     */
    private OutputWriter&MockObject $outputWriterMock;
    /**
     * @var MemoryFilesystem
     */
    private MemoryFilesystem $filesystem;
    /**
     * @var UnnecessaryBaselineDetector
     */
    private UnnecessaryBaselineDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runnerMock = $this->createMock(Runner::class);
        $this->outputWriterMock = $this->createMock(OutputWriter::class);
        $this->filesystem = new MemoryFilesystem();
        $this->detector = new UnnecessaryBaselineDetector(
            $this->createMock(BasePathFinder::class),
            $this->runnerMock,
            new BaselineFactory(),
            $this->filesystem,
            new UnnecessaryBaselineFinder(new InstructionCommentCollector(new InstructionCommentLineParser())),
            $this->outputWriterMock
        );
    }

    public function testDetectsUnnecessaryBaselines(): void
    {
        $this->runnerMock->expects(self::once())->method('run')->with(self::anything(), true)->willReturn(
            ReportFactory::createWithFileReportsByFilename([
                'a.php' => FileReportFactory::createWithMessages([MessageFactory::createWithSourceAndLine('A.B.C', 3)]),
                'b.php' => FileReportFactory::createWithMessages([]),
            ])
        );
        $this->filesystem->replaceContents('a.php', "<?php\n// phpcs:ignore A.B.C -- baseline\necho 1;\n");
        $this->filesystem->replaceContents('b.php', "<?php\n// phpcs:ignore A.B.C -- baseline\necho 1;\n");

        $lines = [];
        $this->outputWriterMock->method('writeLine')->willReturnCallback(static function (string $line) use (&$lines) {
            $lines[] = $line;
        });

        self::assertSame(1, $this->detector->detect());
        self::assertContains('b.php:2 phpcs:ignore A.B.C', $lines);
        self::assertContains('Found 1 unnecessary baseline(s) in 1 file(s).', $lines);
    }
}
