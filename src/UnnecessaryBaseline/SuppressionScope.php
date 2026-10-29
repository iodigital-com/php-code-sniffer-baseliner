<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\UnnecessaryBaseline;

/**
 * A single rule of a phpcs:ignore or phpcs:disable instruction, together with the lines it suppresses.
 */
class SuppressionScope
{
    /**
     * @var int
     */
    private int $commentIndex;
    /**
     * @var string
     */
    private string $rule;
    /**
     * @var int
     */
    private int $firstLine;
    /**
     * @var int|null
     */
    private ?int $lastLine;
    /**
     * @var int|null
     */
    private ?int $enableLine;

    public function __construct(
        int $commentIndex,
        string $rule,
        int $firstLine,
        ?int $lastLine = null,
        ?int $enableLine = null
    ) {
        $this->commentIndex = $commentIndex;
        $this->rule = $rule;
        $this->firstLine = $firstLine;
        $this->lastLine = $lastLine;
        $this->enableLine = $enableLine;
    }

    public function getCommentIndex(): int
    {
        return $this->commentIndex;
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function getFirstLine(): int
    {
        return $this->firstLine;
    }

    /**
     * The last suppressed line, null if the suppression is never ended.
     */
    public function getLastLine(): ?int
    {
        return $this->lastLine;
    }

    /**
     * The line of the phpcs:enable comment that ends the suppression, if any.
     */
    public function getEnableLine(): ?int
    {
        return $this->enableLine;
    }

    public function endedBy(int $enableLine): self
    {
        return new self($this->commentIndex, $this->rule, $this->firstLine, $enableLine - 1, $enableLine);
    }

    public function endedAfter(int $lastLine): self
    {
        return new self($this->commentIndex, $this->rule, $this->firstLine, $lastLine);
    }
}
