<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node;

/**
 * @property Expr      $var  Variable holding object
 * @property Name|Expr $name Property Name
 */
class PropertyFetch extends Expr
{
    /**
     * Constructs a function call node.
     *
     * @param Expr      $var        Variable holding object
     * @param Name|Expr $name       Property name
     * @param array     $attributes Additional attributes
     */
    public function __construct(Expr $var, Node $name, array $attributes = array()) {
        parent::__construct(
            array(
                'var'  => $var,
                'name' => $name
            ),
            $attributes
        );
    }
}