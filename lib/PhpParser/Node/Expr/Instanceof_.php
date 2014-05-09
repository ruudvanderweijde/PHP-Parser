<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Name;
use PhpParser\Node\Expr;
use PhpParser\Node;

/**
 * @property Expr $expr  Expression
 * @property Name|Expr $class Class name
 */
class Instanceof_ extends Expr
{
    /**
     * Constructs an instanceof check node.
     *
     * @param Expr      $expr       Expression
     * @param Name|Expr $class      Class name
     * @param array     $attributes Additional attributes
     */
    public function __construct(Expr $expr, Node $class, array $attributes = array()) {
        parent::__construct(
            array(
                'expr'  => $expr,
                'class' => $class
            ),
            $attributes
        );
    }
}