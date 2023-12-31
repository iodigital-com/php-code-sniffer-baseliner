<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;
use IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Totals;

use function array_map;
use function array_sum;

class ReportFactory
{
    /**
     * @param array<FileReport>|null $fileReportsByFilenameOrDefault
     */
    public static function create(
        ?Totals $totals = null,
        ?array $fileReportsByFilenameOrDefault = null
    ): Report {
        $fileReportsByFilename = $fileReportsByFilenameOrDefault !== null ? $fileReportsByFilenameOrDefault : [];
        return new Report(
            $totals !== null ? $totals : TotalsFactory::create(
                array_sum(array_map(static function (FileReport $fileReport): int {
                    return $fileReport->getErrorCount();
                }, $fileReportsByFilename)),
                array_sum(array_map(static function (FileReport $fileReport): int {
                    return $fileReport->getWarningCount();
                }, $fileReportsByFilename)),
            ),
            $fileReportsByFilename
        );
    }

    /**
     * @param array<FileReport> $fileReportsByFilename
     */
    public static function createWithFileReportsByFilename(array $fileReportsByFilename): Report
    {
        return self::create(null, $fileReportsByFilename);
    }
}
