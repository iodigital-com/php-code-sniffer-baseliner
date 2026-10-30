<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\UnnecessaryBaseline;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionComment;

class LocatedInstructionComment
{
    /**
     * @var InstructionComment
     */
    private InstructionComment $comment;
    /**
     * @var int
     */
    private int $line;
    /**
     * @var bool
     */
    private bool $trailing;

    public function __construct(InstructionComment $comment, int $line, bool $trailing)
    {
        $this->comment = $comment;
        $this->line = $line;
        $this->trailing = $trailing;
    }

    public function getComment(): InstructionComment
    {
        return $this->comment;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Whether the comment is on the same line as the code it applies to.
     */
    public function isTrailing(): bool
    {
        return $this->trailing;
    }

    /**
     * The first line a phpcs:ignore or phpcs:disable instruction applies to.
     */
    public function getFirstAffectedLine(): int
    {
        return $this->trailing ? $this->line : $this->line + 1;
    }
}
