<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner;

use IODigital\CodeSnifferBaseliner\Baseline\BaselineFactory;
use IODigital\CodeSnifferBaseliner\Filesystem\Filesystem;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaseline;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaselineFinder;
use IODigital\CodeSnifferBaseliner\Util\OutputWriter;

use function count;
use function implode;
use function sprintf;
use function strpos;

class UnnecessaryBaselineDetector
{
    /**
     * @var BasePathFinder
     */
    private BasePathFinder $basePathFinder;
    /**
     * @var Runner
     */
    private Runner $runner;
    /**
     * @var BaselineFactory
     */
    private BaselineFactory $baselineFactory;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var UnnecessaryBaselineFinder
     */
    private UnnecessaryBaselineFinder $unnecessaryBaselineFinder;
    /**
     * @var OutputWriter
     */
    private OutputWriter $outputWriter;

    public function __construct(
        BasePathFinder $basePathFinder,
        Runner $runner,
        BaselineFactory $baselineFactory,
        Filesystem $filesystem,
        UnnecessaryBaselineFinder $unnecessaryBaselineFinder,
        OutputWriter $outputWriter
    ) {
        $this->basePathFinder = $basePathFinder;
        $this->runner = $runner;
        $this->baselineFactory = $baselineFactory;
        $this->filesystem = $filesystem;
        $this->unnecessaryBaselineFinder = $unnecessaryBaselineFinder;
        $this->outputWriter = $outputWriter;
    }

    /**
     * Reports the baselines that are no longer needed.
     *
     * @return int The number of unnecessary baselines found
     */
    public function detect(bool $includeNonBaseline = false): int
    {
        $basePath = $this->basePathFinder->findBasePath();

        $this->outputWriter->writeLine('Running PHP_CodeSniffer ignoring annotations (this may take a while)...');

        $report = $this->runner->runIgnoringAnnotations($basePath);
        $violatedRulesByFileAndLineNumber = $this->baselineFactory
            ->createFromReport($report)
            ->getViolatedRulesByFileAndLineNumber();

        $total = 0;
        $totalFiles = 0;
        foreach ($violatedRulesByFileAndLineNumber as $file => $violatedRulesByLineNumber) {
            $found = $this->reportForFile((string) $file, $violatedRulesByLineNumber, $includeNonBaseline);
            $total += $found;
            $totalFiles += $found > 0 ? 1 : 0;
        }

        if ($total === 0) {
            $this->outputWriter->writeLine('No unnecessary baselines found.');
        } else {
            $this->outputWriter->writeLine(sprintf(
                'Found %d unnecessary %s in %d %s.',
                $total,
                $total === 1 ? 'baseline' : 'baselines',
                $totalFiles,
                $totalFiles === 1 ? 'file' : 'files'
            ));
        }
        return $total;
    }

    /**
     * @param array<int, array<string>> $violatedRulesByLineNumber
     */
    private function reportForFile(string $file, array $violatedRulesByLineNumber, bool $includeNonBaseline): int
    {
        $sourceCode = $this->filesystem->readContents($file);
        if (strpos($sourceCode, 'phpcs:') === false) {
            return 0;
        }
        $unnecessaryBaselines = $this->unnecessaryBaselineFinder->find(
            $sourceCode,
            $violatedRulesByLineNumber,
            $includeNonBaseline
        );
        foreach ($unnecessaryBaselines as $unnecessaryBaseline) {
            $this->outputWriter->writeLine($this->format($file, $unnecessaryBaseline));
        }
        return count($unnecessaryBaselines);
    }

    private function format(string $file, UnnecessaryBaseline $unnecessaryBaseline): string
    {
        $rules = count($unnecessaryBaseline->getRules()) > 0
            ? sprintf(' %s', implode(', ', $unnecessaryBaseline->getRules()))
            : '';
        if ($unnecessaryBaseline->getInstruction() === 'disable') {
            $enableLine = $unnecessaryBaseline->getEnableLine();
            return sprintf(
                '%s:%d phpcs:disable%s (%s)',
                $file,
                $unnecessaryBaseline->getLine(),
                $rules,
                $enableLine !== null ? sprintf('phpcs:enable on line %d', $enableLine) : 'no phpcs:enable'
            );
        }
        return sprintf('%s:%d phpcs:ignore%s', $file, $unnecessaryBaseline->getLine(), $rules);
    }
}
