<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner;

use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;

class RunnerFactory
{
    public static function create(): Runner
    {
        return new Runner();
    }
}
