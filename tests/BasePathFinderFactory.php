<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests;

use IODigital\CodeSnifferBaseliner\BasePathFinder;

class BasePathFinderFactory
{
    public static function create(): BasePathFinder
    {
        return new BasePathFinder();
    }
}
