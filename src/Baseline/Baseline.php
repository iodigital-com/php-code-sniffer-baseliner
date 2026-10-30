<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Baseline;

class Baseline
{
    /**
     * @var array<string, array<int, array<string>>>
     */
    private array $violatedRulesByFileAndLineNumber;

    /**
     * @param array<string, array<int, array<string>>> $violatedRulesByFileAndLineNumber
     */
    public function __construct(array $violatedRulesByFileAndLineNumber)
    {
        $this->violatedRulesByFileAndLineNumber = $violatedRulesByFileAndLineNumber;
    }

    /**
     * @return array<string, array<int, array<string>>>
     */
    public function getViolatedRulesByFileAndLineNumber(): array
    {
        return $this->violatedRulesByFileAndLineNumber;
    }
}
