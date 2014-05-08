<?php

namespace PhpParser\Node\Stmt\TraitUseAdaptation;

use PhpParser\Node;
use PhpParser\Node\Name;

/**
 * @property Name   $trait     Trait name
 * @property string $method    Method name
 * @property Name[] $insteadof Overwritten traits
 */
class Precedence extends Node\Stmt\TraitUseAdaptation
{
    /**
     * Constructs a trait use precedence adaptation node.
     *
     * @param Name   $trait       Trait name
     * @param Name   $method      Method name
     * @param Name[] $insteadof   Overwritten traits
     * @param array       $attributes  Additional attributes
     */
    public function __construct(Name $trait, Name $method, array $insteadof, array $attributes = array()) {
        parent::__construct(
            array(
                'trait'     => $trait,
                'method'    => $method,
                'insteadof' => $insteadof,
            ),
            $attributes
        );
    }
}