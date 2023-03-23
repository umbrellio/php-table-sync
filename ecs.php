<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/app', __DIR__ . '/tests']);
    $ecsConfig->parallel();

    $ecsConfig->sets([SetList::PSR_12]);
    $ecsConfig->sets([SetList::CLEAN_CODE]);
    $ecsConfig->sets([SetList::COMMON]);
    $ecsConfig->sets([SetList::SYMPLIFY]);
    $ecsConfig->sets([SetList::STRICT]);

    $ecsConfig->skip([
        'vendor/*',
        'database/*',
        '.ecs_cache/*',

        ArrayIndentationFixer::class,
        MethodArgumentSpaceFixer::class,
        SingleTraitInsertPerStatementFixer::class,
        PhpdocTrimFixer::class,
        AssignmentInConditionSniff::class,
        MethodChainingNewlineFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        PhpdocLineSpanFixer::class,
        MethodChainingIndentationFixer::class,
        ClassAttributesSeparationFixer::class,
        LineLengthFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        ExplicitStringVariableFixer::class,
    ]);
};
