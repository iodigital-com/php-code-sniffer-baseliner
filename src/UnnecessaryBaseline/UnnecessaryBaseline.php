<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\UnnecessaryBaseline;

class UnnecessaryBaseline
{
    /**
     * @var string
     */
    private string $instruction;
    /**
     * @var int
     */
    private int $line;
    /**
     * @var int|null
     */
    private ?int $enableLine;
    /**
     * @var array<string>
     */
    private array $rules;

    /**
     * @param array<string> $rules The unnecessary rules, an empty array means all rules of the instruction
     */
    public function __construct(string $instruction, int $line, ?int $enableLine, array $rules)
    {
        $this->instruction = $instruction;
        $this->line = $line;
        $this->enableLine = $enableLine;
        $this->rules = $rules;
    }

    /**
     * Either 'ignore' or 'disable'.
     */
    public function getInstruction(): string
    {
        return $this->instruction;
    }

    /**
     * The line number of the phpcs:ignore or phpcs:disable comment.
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * The line number of the matching phpcs:enable comment, if any.
     */
    public function getEnableLine(): ?int
    {
        return $this->enableLine;
    }

    /**
     * @return array<string>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
