<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report;

class FileReport
{
    /**
     * @var int
     */
    private int $errorCount;
    /**
     * @var int
     */
    private int $warningCount;
    /**
     * @var array<Message>
     */
    private array $messages;

    /**
     * @param array<Message> $messages
     */
    public function __construct(int $errorCount, int $warningCount, array $messages)
    {
        $this->errorCount = $errorCount;
        $this->warningCount = $warningCount;
        $this->messages = $messages;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getWarningCount(): int
    {
        return $this->warningCount;
    }

    /**
     * @return array<Message>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
