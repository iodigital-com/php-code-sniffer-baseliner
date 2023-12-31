<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Message;

use function array_filter;
use function count;

class FileReportFactory
{
    /**
     * @param array<Message>|null $messagesOrDefault
     */
    public static function create(
        ?int $errorCount = null,
        ?int $warningCount = null,
        ?array $messagesOrDefault = null
    ): FileReport {
        $messages = $messagesOrDefault !== null ? $messagesOrDefault : [];
        return new FileReport(
            $errorCount !== null ?
                $errorCount :
                count(array_filter($messages, static function (Message $message): bool {
                    return $message->isError();
                })),
            $warningCount !== null ?
                $warningCount :
                count(array_filter($messages, static function (Message $message): bool {
                    return $message->isWarning();
                })),
            $messages
        );
    }

    /**
     * @param array<Message>|null $messages
     */
    public static function createWithMessages(?array $messages = null): FileReport
    {
        return self::create(null, null, $messages);
    }
}
