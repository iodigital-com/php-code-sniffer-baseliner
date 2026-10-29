<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class AddBaselineProcessorTest extends TestCase
{
    /**
     * @var AddBaselineProcessor
     */
    private AddBaselineProcessor $addBaselineProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addBaselineProcessor = AddBaselineProcessorFactory::create();
    }

    /**
     * @param array<array<string>> $ruleExclusionsByLineNumber
     */
    #[DataProviderExternal(AddBaselineProcessorTestDataProvider::class, 'provide')]
    public function testAddRuleExclusionsByLineNumber(
        string $fileContents,
        array $ruleExclusionsByLineNumber,
        string $expectedModifiedFileContents
    ): void {
        $modifiedFileContents = $this->addBaselineProcessor->addRuleExclusionsByLineNumber(
            $fileContents,
            $ruleExclusionsByLineNumber
        );

        self::assertSame($expectedModifiedFileContents, $modifiedFileContents);
    }
}
