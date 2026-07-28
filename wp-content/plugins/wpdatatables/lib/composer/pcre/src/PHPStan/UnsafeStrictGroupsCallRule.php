<?php

declare (strict_types=1);
namespace WPDT\Composer\Pcre\PHPStan;

use WPDT\Composer\Pcre\Preg;
use WPDT\Composer\Pcre\Regex;
use WPDT\PhpParser\Node;
use WPDT\PhpParser\Node\Expr\StaticCall;
use WPDT\PhpParser\Node\Name\FullyQualified;
use WPDT\PHPStan\Analyser\Scope;
use WPDT\PHPStan\Analyser\SpecifiedTypes;
use WPDT\PHPStan\Rules\Rule;
use WPDT\PHPStan\Rules\RuleErrorBuilder;
use WPDT\PHPStan\TrinaryLogic;
use WPDT\PHPStan\Type\ObjectType;
use WPDT\PHPStan\Type\Type;
use WPDT\PHPStan\Type\TypeCombinator;
use WPDT\PHPStan\Type\Php\RegexArrayShapeMatcher;
use function sprintf;
/**
 * @implements Rule<StaticCall>
 */
final class UnsafeStrictGroupsCallRule implements Rule
{
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function getNodeType() : string
    {
        return StaticCall::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!$node->class instanceof FullyQualified) {
            return [];
        }
        $isRegex = $node->class->toString() === Regex::class;
        $isPreg = $node->class->toString() === Preg::class;
        if (!$isRegex && !$isPreg) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier || !\in_array($node->name->name, ['matchStrictGroups', 'isMatchStrictGroups', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true)) {
            return [];
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return [];
        }
        $patternArg = $args[0] ?? null;
        if ($isPreg) {
            if (!isset($args[2])) {
                // no matches set, skip as the matches won't be used anyway
                return [];
            }
            $flagsArg = $args[3] ?? null;
        } else {
            $flagsArg = $args[2] ?? null;
        }
        if ($patternArg === null) {
            return [];
        }
        $flagsType = PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return [];
        }
        $matchedType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createYes(), $scope);
        if ($matchedType === null) {
            return [RuleErrorBuilder::message(sprintf('The %s call is potentially unsafe as $matches\' type could not be inferred.', $node->name->name))->identifier('composerPcre.maybeUnsafeStrictGroups')->build()];
        }
        if (\count($matchedType->getConstantArrays()) === 1) {
            $matchedType = $matchedType->getConstantArrays()[0];
            $nullableGroups = [];
            foreach ($matchedType->getValueTypes() as $index => $type) {
                if (TypeCombinator::containsNull($type)) {
                    $nullableGroups[] = $matchedType->getKeyTypes()[$index]->getValue();
                }
            }
            if (\count($nullableGroups) > 0) {
                return [RuleErrorBuilder::message(sprintf('The %s call is unsafe as match group%s "%s" %s optional and may be null.', $node->name->name, \count($nullableGroups) > 1 ? 's' : '', \implode('", "', $nullableGroups), \count($nullableGroups) > 1 ? 'are' : 'is'))->identifier('composerPcre.unsafeStrictGroups')->build()];
            }
        }
        return [];
    }
}
