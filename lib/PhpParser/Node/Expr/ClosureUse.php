<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * @property string $var   Name of variable
 * @property bool   $byRef Whether to use by reference
 */
class ClosureUse extends Expr
{
    /**
     * Constructs a closure use node.
     *
     * @param Variable  $var Name of variable
     * @param bool $byRef Whether to use by reference
     * @param array $attributes Additional attributes
     */
    public function __construct(Variable $var, $byRef = false, array $attributes = array()) {
        parent::__construct(
            array(
                'var'   => $var,
                'byRef' => $byRef
            ),
            $attributes
        );
    }
}