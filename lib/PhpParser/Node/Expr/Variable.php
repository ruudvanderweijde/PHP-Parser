<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * @property string|Expr $name Name
 */
class Variable extends Expr
{
    /**
     * Constructs a variable node.
     *
     * @param Name|Expr $name       Name
     * @param array     $attributes Additional attributes
     */
    public function __construct(Node $name, array $attributes = array()) {
        parent::__construct(
            array(
                 'name' => $name
            ),
            $attributes
        );
    }
}