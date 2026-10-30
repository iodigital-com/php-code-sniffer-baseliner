<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner;

use Exception;
use InvalidArgumentException;
use IODigital\CodeSnifferBaseliner\Baseline\BaselineFactory;
use IODigital\CodeSnifferBaseliner\Command\CreateBaseline;
use IODigital\CodeSnifferBaseliner\Command\DetectUnnecessaryBaselines;
use IODigital\CodeSnifferBaseliner\Command\ShowHelp;
use IODigital\CodeSnifferBaseliner\Filesystem\NativeFilesystem;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\InstructionCommentCollector;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaselineFinder;
use IODigital\CodeSnifferBaseliner\Util\OutputWriter;
use Throwable;

use function array_shift;
use function count;
use function sprintf;

use const PHP_EOL;

class Application
{
    public static function create(): self
    {
        return new self(
            new BaselineCreator(
                new BasePathFinder(),
                new Runner(),
                new BaselineFactory(),
                new NativeFilesystem(),
                new AddBaselineProcessor(new InstructionCommentLineParser()),
                new OutputWriter()
            ),
            new UnnecessaryBaselineDetector(
                new BasePathFinder(),
                new Runner(),
                new BaselineFactory(),
                new NativeFilesystem(),
                new UnnecessaryBaselineFinder(new InstructionCommentCollector(new InstructionCommentLineParser())),
                new OutputWriter()
            )
        );
    }

    /**
     * @var BaselineCreator
     */
    private BaselineCreator $baselineCreator;

    /**
     * @var UnnecessaryBaselineDetector
     */
    private UnnecessaryBaselineDetector $unnecessaryBaselineDetector;

    public function __construct(
        BaselineCreator $baselineCreator,
        UnnecessaryBaselineDetector $unnecessaryBaselineDetector
    ) {
        $this->baselineCreator = $baselineCreator;
        $this->unnecessaryBaselineDetector = $unnecessaryBaselineDetector;
    }

    public function run(string ...$arguments): int
    {
        try {
            $command = $this->getCommandForArguments(...$arguments);
            return $this->runCommand($command);
        } catch (Throwable $throwable) {
            echo ((string) $throwable) . PHP_EOL;
            return 1;
        }
    }

    /**
     * @throws Exception
     */
    private function getCommandForArguments(string ...$arguments): object
    {
        if (count($arguments) === 0) {
            throw new InvalidArgumentException('Please provide at least one argument.');
        }
        $binaryName = array_shift($arguments);
        if (count($arguments) === 0) {
            return $this->getShowHelpCommand();
        }
        $commandName = array_shift($arguments);
        switch ($commandName) {
            case 'create-baseline':
                return new CreateBaseline();
            case 'detect-unnecessary-baselines':
                return $this->getDetectUnnecessaryBaselinesCommand(...$arguments);
            case 'help':
            default:
                return $this->getShowHelpCommand($binaryName);
        }
    }

    private function getDetectUnnecessaryBaselinesCommand(string ...$options): DetectUnnecessaryBaselines
    {
        $includeNonBaseline = false;
        foreach ($options as $option) {
            if ($option !== '--include-non-baseline') {
                throw new InvalidArgumentException(sprintf('Unknown option \'%s\'.', $option));
            }
            $includeNonBaseline = true;
        }
        return new DetectUnnecessaryBaselines($includeNonBaseline);
    }

    private function getShowHelpCommand(?string $commandName = null): ShowHelp
    {
        return new ShowHelp($commandName !== null ? $commandName : 'phpcs-baseliner');
    }

    private function runCommand(object $command): int
    {
        if ($command instanceof CreateBaseline) {
            $this->baselineCreator->create();
        } elseif ($command instanceof DetectUnnecessaryBaselines) {
            $found = $this->unnecessaryBaselineDetector->detect($command->includesNonBaseline());
            return $found > 0 ? 1 : 0;
        } elseif ($command instanceof ShowHelp) {
            $this->showHelp($command);
        } else {
            throw new InvalidArgumentException(
                sprintf('Unable to handle command of type \'%s\'.', $command::class)
            );
        }
        return 0;
    }

    private function showHelp(ShowHelp $command): void
    {
        echo sprintf(
            'Usage: %1$s create-baseline' . PHP_EOL
            . '       %1$s detect-unnecessary-baselines [--include-non-baseline]',
            $command->getCommandName()
        );
    }
}
