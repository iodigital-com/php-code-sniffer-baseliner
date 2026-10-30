<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Command;

class DetectUnnecessaryBaselines
{
    /**
     * @var bool
     */
    private bool $includeNonBaseline;

    public function __construct(bool $includeNonBaseline = false)
    {
        $this->includeNonBaseline = $includeNonBaseline;
    }

    public function includesNonBaseline(): bool
    {
        return $this->includeNonBaseline;
    }
}
