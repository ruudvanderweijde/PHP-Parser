<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * @property Name|Expr      $class Class name
 * @property Name|Expr      $name  Method name
 * @property Node\Arg[]     $args  Arguments
 */
class StaticCall extends Expr
{
    /**
     * Constructs a static method call node.
     *
     * @param Name|Expr      $class      Class name
     * @param Name|Expr      $name       Method name
     * @param Node\Arg[]     $args       Arguments
     * @param array          $attributes Additional attributes
     */
    public function __construct(Node $class, Node $name, array $args = array(), array $attributes = array()) {
        parent::__construct(
            array(
                'class' => $class,
                'name'  => $name,
                'args'  => $args
            ),
            $attributes
        );
    }
}