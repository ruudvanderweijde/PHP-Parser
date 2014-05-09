<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Name;
use PhpParser\Node\Expr;
use PhpParser\Node;

/**
 * @property Name|Expr $class Class name
 * @property Name|Expr $name  Property name
 */
class StaticPropertyFetch extends Expr
{
    /**
     * Constructs a static property fetch node.
     *
     * @param Name|Expr   $class      Class name
     * @param Name|Expr   $name       Property name
     * @param array       $attributes Additional attributes
     */
    public function __construct(Node $class, Node $name, array $attributes = array()) {
        parent::__construct(
            array(
                'class' => $class,
                'name'  => $name
            ),
            $attributes
        );
    }
}