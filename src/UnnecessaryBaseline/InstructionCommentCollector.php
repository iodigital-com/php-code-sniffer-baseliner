<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\UnnecessaryBaseline;

use IODigital\CodeSnifferBaseliner\PhpTokenizer\Token;
use IODigital\CodeSnifferBaseliner\PhpTokenizer\Tokenizer;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;

use function array_key_exists;
use function explode;
use function in_array;
use function preg_match;
use function rtrim;
use function sprintf;
use function substr;

use const T_COMMENT;
use const T_DOC_COMMENT;

class InstructionCommentCollector
{
    private const BLOCK_COMMENT_LINE_REGULAR_EXPRESSION
        = '`^\\s*(?:/\\*+|\\*)?\\s*(?<instruction>phpcs:.*?)\\s*(?:\\*+/)?$`';

    /**
     * @var InstructionCommentLineParser
     */
    private InstructionCommentLineParser $instructionCommentParser;

    public function __construct(InstructionCommentLineParser $instructionCommentParser)
    {
        $this->instructionCommentParser = $instructionCommentParser;
    }

    /**
     * @return array<LocatedInstructionComment>
     */
    public function collect(string $sourceCode): array
    {
        $comments = [];
        $linesWithCode = [];
        foreach ((new Tokenizer())->tokenize($sourceCode) as $token) {
            if (!in_array($token->getType(), [T_COMMENT, T_DOC_COMMENT], true)) {
                if (!$token->isWhiteSpace()) {
                    $linesWithCode[$token->getStartingLineNumber()] = true;
                }
                continue;
            }
            $trailing = array_key_exists($token->getStartingLineNumber(), $linesWithCode);
            foreach ($this->parseComment($token, $trailing) as $comment) {
                $comments[] = $comment;
            }
        }
        return $comments;
    }

    /**
     * @return array<LocatedInstructionComment>
     */
    private function parseComment(Token $token, bool $trailing): array
    {
        $contents = $token->getContents();
        $isLineComment = substr($contents, 0, 2) === '//';
        $found = [];
        foreach (explode("\n", rtrim($contents, "\r\n")) as $offset => $commentLine) {
            $instructionLine = $this->extractInstructionLine(rtrim($commentLine, "\r"), $isLineComment);
            $comment = $instructionLine !== null ? $this->instructionCommentParser->parse($instructionLine) : null;
            if ($comment !== null) {
                $found[] = new LocatedInstructionComment(
                    $comment,
                    $token->getStartingLineNumber() + $offset,
                    $trailing && $offset === 0
                );
            }
        }
        return $found;
    }

    private function extractInstructionLine(string $commentLine, bool $isLineComment): ?string
    {
        if ($isLineComment) {
            return $commentLine;
        }
        if (preg_match(self::BLOCK_COMMENT_LINE_REGULAR_EXPRESSION, $commentLine, $matches) !== 1) {
            return null;
        }
        return sprintf('// %s', $matches['instruction']);
    }
}
