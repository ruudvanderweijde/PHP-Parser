<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node;

/**
 * @property Expr        $var  Variable holding object
 * @property Name|Expr   $name Method name
 * @property Arg[]       $args Arguments
 */
class MethodCall extends Expr
{
    /**
     * Constructs a function call node.
     *
     * @param Expr        $var        Variable holding object
     * @param Name|Expr   $name       Method name
     * @param Arg[]       $args       Arguments
     * @param array       $attributes Additional attributes
     */
    public function __construct(Expr $var, Node $name, array $args = array(), array $attributes = array()) {
        parent::__construct(
            array(
                'var'  => $var,
                'name' => $name,
                'args' => $args
            ),
            $attributes
        );
    }
}