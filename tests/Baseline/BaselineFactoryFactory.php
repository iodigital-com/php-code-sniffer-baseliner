<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\Baseline;

use IODigital\CodeSnifferBaseliner\Baseline\BaselineFactory;

class BaselineFactoryFactory
{
    public static function create(): BaselineFactory
    {
        return new BaselineFactory();
    }
}
