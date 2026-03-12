<?php

declare(strict_types=1);

namespace App\StaticAnalysis\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Interface_>
 */
final class InterfaceShouldNotUseInterfaceSuffixRule implements Rule
{
    public function getNodeType(): string
    {
        return Interface_::class;
    }

    /**
     * @param  Interface_  $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name === null) {
            return [];
        }

        $interfaceName = $node->name->toString();

        if (! str_ends_with($interfaceName, 'Interface')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Interface "%s" should not use the "Interface" suffix.',
                $interfaceName,
            ))
                ->identifier('project.interface.noSuffix')
                ->build(),
        ];
    }
}
