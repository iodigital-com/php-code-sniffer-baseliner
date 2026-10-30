<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\UnnecessaryBaseline;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionComment;

use function array_key_exists;
use function in_array;
use function strpos;

class UnnecessaryBaselineFinder
{
    private const BASELINE_MESSAGE = 'baseline';
    private const ALL_RULES = '';

    /**
     * @var InstructionCommentCollector
     */
    private InstructionCommentCollector $instructionCommentCollector;

    public function __construct(InstructionCommentCollector $instructionCommentCollector)
    {
        $this->instructionCommentCollector = $instructionCommentCollector;
    }

    /**
     * Finds the phpcs:ignore and phpcs:disable instructions (or rules thereof) that do not suppress any violation.
     *
     * @param array<int, array<string>> $violatedRulesByLineNumber Violations reported with annotations being ignored
     * @return array<UnnecessaryBaseline>
     */
    public function find(string $sourceCode, array $violatedRulesByLineNumber, bool $includeNonBaseline): array
    {
        $comments = $this->instructionCommentCollector->collect($sourceCode);

        $unnecessaryRulesByComment = [];
        $enableLinesByComment = [];
        foreach ($this->createScopes($comments, $includeNonBaseline) as $scope) {
            if ($scope->getEnableLine() !== null) {
                $enableLinesByComment[$scope->getCommentIndex()] = $scope->getEnableLine();
            }
            if (!$this->isViolated($scope, $violatedRulesByLineNumber)) {
                $unnecessaryRulesByComment[$scope->getCommentIndex()][] = $scope->getRule();
            }
        }

        $unnecessaryBaselines = [];
        foreach ($unnecessaryRulesByComment as $index => $unnecessaryRules) {
            $unnecessaryBaselines[] = new UnnecessaryBaseline(
                $comments[$index]->getComment()->getInstruction(),
                $comments[$index]->getLine(),
                array_key_exists($index, $enableLinesByComment) ? $enableLinesByComment[$index] : null,
                $unnecessaryRules === [self::ALL_RULES] ? [] : $unnecessaryRules
            );
        }
        return $unnecessaryBaselines;
    }

    /**
     * @param array<LocatedInstructionComment> $comments
     * @return array<SuppressionScope>
     */
    private function createScopes(array $comments, bool $includeNonBaseline): array
    {
        $scopes = [];
        $openDisables = [];
        foreach ($comments as $index => $located) {
            $comment = $located->getComment();
            if ($comment->getInstruction() === 'enable') {
                $this->closeDisables($openDisables, $scopes, $this->getRulesOrAll($comment), $located->getLine());
            } elseif ($this->isBaseline($comment, $includeNonBaseline)) {
                $this->openScopes($openDisables, $scopes, $index, $located);
            }
        }

        // Disables that are never enabled
        foreach ($openDisables as $scope) {
            $scopes[] = $scope;
        }
        return $scopes;
    }

    /**
     * @param array<string, SuppressionScope> $openDisables
     * @param array<SuppressionScope> $scopes
     */
    private function openScopes(
        array &$openDisables,
        array &$scopes,
        int $index,
        LocatedInstructionComment $located
    ): void {
        $firstLine = $located->getFirstAffectedLine();
        foreach ($this->getRulesOrAll($located->getComment()) as $rule) {
            $scope = new SuppressionScope($index, $rule, $firstLine);
            if ($located->getComment()->getInstruction() === 'ignore') {
                $scopes[] = $scope->endedAfter($firstLine);
            } elseif (!array_key_exists($rule, $openDisables)) {
                $openDisables[$rule] = $scope;
            }
        }
    }

    /**
     * @param array<string, SuppressionScope> $openDisables
     * @param array<SuppressionScope> $scopes
     * @param array<string> $enabledRules
     */
    private function closeDisables(array &$openDisables, array &$scopes, array $enabledRules, int $enableLine): void
    {
        foreach ($openDisables as $disabledRule => $scope) {
            foreach ($enabledRules as $enabledRule) {
                if ($this->ruleCovers($enabledRule, $disabledRule)) {
                    $scopes[] = $scope->endedBy($enableLine);
                    unset($openDisables[$disabledRule]);
                    break;
                }
            }
        }
    }

    /**
     * @param array<int, array<string>> $violatedRulesByLineNumber
     */
    private function isViolated(SuppressionScope $scope, array $violatedRulesByLineNumber): bool
    {
        foreach ($violatedRulesByLineNumber as $lineNumber => $sources) {
            if ($lineNumber < $scope->getFirstLine()) {
                continue;
            }
            if ($scope->getLastLine() !== null && $lineNumber > $scope->getLastLine()) {
                continue;
            }
            foreach ($sources as $source) {
                if ($this->ruleCovers($scope->getRule(), $source)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Like PHP_CodeSniffer, a rule covers a source if it is equal to the source or one of its parents,
     * e.g. Generic.Files covers Generic.Files.LineLength.TooLong.
     */
    private function ruleCovers(string $rule, string $source): bool
    {
        return $rule === self::ALL_RULES
            || $rule === $source
            || strpos($source, $rule . '.') === 0;
    }

    /**
     * @return array<string>
     */
    private function getRulesOrAll(InstructionComment $comment): array
    {
        return $comment->hasRules() ? $comment->getRules() : [self::ALL_RULES];
    }

    private function isBaseline(InstructionComment $comment, bool $includeNonBaseline): bool
    {
        return $includeNonBaseline || in_array(self::BASELINE_MESSAGE, $comment->getMessages(), true);
    }
}
