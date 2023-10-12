<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests;

use IODigital\CodeSnifferBaseliner\Baseline\BaselineFactory;
use IODigital\CodeSnifferBaseliner\BaselineCreator;
use IODigital\CodeSnifferBaseliner\BasePathFinder;
use IODigital\CodeSnifferBaseliner\Filesystem\Filesystem;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use IODigital\CodeSnifferBaseliner\Tests\Filesystem\FilesystemFactory;
use IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\RunnerFactory;
use IODigital\CodeSnifferBaseliner\Tests\SourceCodeProcessor\AddBaselineProcessorFactory;
use IODigital\CodeSnifferBaseliner\Util\OutputWriter;

class BaselinerCreatorFactory
{
    public static function create(
        ?BasePathFinder $basePathFinder = null,
        ?Runner $runner = null,
        ?BaselineFactory $baselineFactory = null,
        ?Filesystem $filesystem = null,
        ?AddBaselineProcessor $addBaselineProcessor = null,
        ?OutputWriter $outputWriter = null
    ): BaselineCreator {
        return new BaselineCreator(
            $basePathFinder !== null ? $basePathFinder : BasePathFinderFactory::create(),
            $runner !== null ? $runner : RunnerFactory::create(),
            $baselineFactory !== null ? $baselineFactory : Baseline\BaselineFactoryFactory::create(),
            $filesystem !== null ? $filesystem : FilesystemFactory::create(),
            $addBaselineProcessor !== null ? $addBaselineProcessor : AddBaselineProcessorFactory::create(),
            $outputWriter !== null ? $outputWriter : Util\OutputWriterFactory::create()
        );
    }
}
