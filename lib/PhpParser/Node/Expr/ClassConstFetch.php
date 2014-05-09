<?php

namespace PhpParser\Node\Expr;

use PhpParser\Node\Name;
use PhpParser\Node\Expr;
use PhpParser\Node;

/**
 * @property Name|Expr $class Class name
 * @property string    $name  Constant name
 */
class ClassConstFetch extends Expr
{
    /**
     * Constructs a class const fetch node.
     *
     * @param Name|Expr $class      Class name
     * @param Name      $name       Constant name
     * @param array     $attributes Additional attributes
     */
    public function __construct(Node $class, Name $name, array $attributes = array()) {
        parent::__construct(
            array(
                'class' => $class,
                'name'  => $name
            ),
            $attributes
        );
    }
}