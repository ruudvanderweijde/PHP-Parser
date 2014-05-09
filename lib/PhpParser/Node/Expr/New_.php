<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * @property Name|Expr  $class Class name
 * @property Node\Arg[] $args  Arguments
 */
class New_ extends Expr
{
    /**
     * Constructs a function call node.
     *
     * @param Name|Expr  $class      Class name
     * @param Node\Arg[] $args       Arguments
     * @param array      $attributes Additional attributes
     */
    public function __construct(Node $class, array $args = array(), array $attributes = array()) {
        parent::__construct(
            array(
                'class' => $class,
                'args'  => $args
            ),
            $attributes
        );
    }
}