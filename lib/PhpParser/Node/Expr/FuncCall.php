<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * @property Name|Expr  $name Function name
 * @property Node\Arg[] $args Arguments
 */
class FuncCall extends Expr
{
    /**
     * Constructs a function call node.
     *
     * @param Name|Expr  $name       Function name
     * @param Node\Arg[] $args       Arguments
     * @param array      $attributes Additional attributes
     */
    public function __construct(Node $name, array $args = array(), array $attributes = array()) {
        parent::__construct(
            array(
                'name' => $name,
                'args' => $args
            ),
            $attributes
        );
    }
}