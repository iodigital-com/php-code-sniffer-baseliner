<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report;

class Totals
{
    private int $errors;
    private int $warnings;
    private int $fixable;

    public function __construct(int $errors, int $warnings, int $fixable)
    {
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->fixable = $fixable;
    }

    public function getErrors(): int
    {
        return $this->errors;
    }

    public function getWarnings(): int
    {
        return $this->warnings;
    }

    public function getFixable(): int
    {
        return $this->fixable;
    }
}
