<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\UnnecessaryBaseline;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\InstructionCommentCollector;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaseline;
use IODigital\CodeSnifferBaseliner\UnnecessaryBaseline\UnnecessaryBaselineFinder;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * @phpstan-type ExpectedBaseline array{string, int, int|null, array<string>}
 */
class UnnecessaryBaselineFinderTest extends TestCase
{
    /**
     * @param array<int, array<string>> $violations
     * @param array<ExpectedBaseline> $expected
     * @dataProvider provide
     */
    public function testFind(string $source, array $violations, bool $includeNonBaseline, array $expected): void
    {
        $finder = new UnnecessaryBaselineFinder(new InstructionCommentCollector(new InstructionCommentLineParser()));

        $actual = array_map(static function (UnnecessaryBaseline $baseline): array {
            return [
                $baseline->getInstruction(),
                $baseline->getLine(),
                $baseline->getEnableLine(),
                $baseline->getRules(),
            ];
        }, $finder->find($source, $violations, $includeNonBaseline));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provide(): iterable
    {
        yield from self::provideIgnores();
        yield from self::provideDisables();
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provideIgnores(): iterable
    {
        yield 'needed ignore' => [
            "<?php\n// phpcs:ignore A.B.C -- baseline\necho 1;\n",
            [3 => ['A.B.C']],
            false,
            [],
        ];
        yield 'unneeded ignore' => [
            "<?php\n// phpcs:ignore A.B.C -- baseline\necho 1;\n",
            [],
            false,
            [['ignore', 2, null, ['A.B.C']]],
        ];
        yield 'only some rules unneeded' => [
            "<?php\n// phpcs:ignore A.B.C, D.E.F -- baseline\necho 1;\n",
            [3 => ['D.E.F']],
            false,
            [['ignore', 2, null, ['A.B.C']]],
        ];
        yield from self::provideCoverageIgnores();
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provideCoverageIgnores(): iterable
    {
        yield 'parent rule covers source' => [
            "<?php\n// phpcs:ignore A.B -- baseline\necho 1;\n",
            [3 => ['A.B.C']],
            false,
            [],
        ];
        yield 'violation on wrong line' => [
            "<?php\n// phpcs:ignore A.B.C -- baseline\necho 1;\necho 2;\n",
            [4 => ['A.B.C']],
            false,
            [['ignore', 2, null, ['A.B.C']]],
        ];
        yield from self::provideMoreIgnores();
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provideMoreIgnores(): iterable
    {
        yield 'trailing ignore on first line' => [
            "<?php // phpcs:ignore A.B.C -- baseline\necho 1;\n",
            [1 => ['A.B.C']],
            false,
            [],
        ];
        yield 'ignore without rules is unneeded' => [
            "<?php\n// phpcs:ignore -- baseline\necho 1;\n",
            [],
            false,
            [['ignore', 2, null, []]],
        ];
        yield from self::provideNonBaselineIgnores();
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provideNonBaselineIgnores(): iterable
    {
        yield 'non-baseline ignored by default' => [
            "<?php\n// phpcs:ignore A.B.C\necho 1;\n",
            [],
            false,
            [],
        ];
        yield 'non-baseline included on request' => [
            "<?php\n// phpcs:ignore A.B.C\necho 1;\n",
            [],
            true,
            [['ignore', 2, null, ['A.B.C']]],
        ];
    }

    /**
     * @return iterable<string, array{string, array<int, array<string>>, bool, array<ExpectedBaseline>}>
     */
    public static function provideDisables(): iterable
    {
        yield 'needed disable and enable' => [
            "<?php\n// phpcs:disable A.B.C -- baseline\n\$a = '\n';\n// phpcs:enable A.B.C -- baseline\n",
            [3 => ['A.B.C']],
            false,
            [],
        ];
        yield 'violation after enable does not count' => [
            "<?php\n// phpcs:disable A.B.C -- baseline\n\$a = 1;\n// phpcs:enable A.B.C -- baseline\necho 1;\n",
            [5 => ['A.B.C']],
            false,
            [['disable', 2, 4, ['A.B.C']]],
        ];
        yield 'disable without enable' => [
            "<?php\n// phpcs:disable A.B.C -- baseline\necho 1;\n",
            [],
            false,
            [['disable', 2, null, ['A.B.C']]],
        ];
        yield 'block comment' => [
            "<?php\n/*\n * phpcs:ignore A.B.C -- baseline\n */\necho 1;\n",
            [],
            false,
            [['ignore', 3, null, ['A.B.C']]],
        ];
    }
}
