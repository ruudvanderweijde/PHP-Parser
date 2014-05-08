<?php

namespace PhpParser\Node\Stmt\TraitUseAdaptation;

use PhpParser\Node;
use PhpParser\Node\Name;

/**
 * @property null|Name $trait       Trait name
 * @property Name      $method      Method name
 * @property null|int  $newModifier New modifier
 * @property null|Name $newName     New name
 */
class Alias extends Node\Stmt\TraitUseAdaptation
{
    /**
     * Constructs a trait use precedence adaptation node.
     *
     * @param null|Name $trait       Trait name
     * @param Node\Name $method      Method name
     * @param null|int  $newModifier New modifier
     * @param null|Name $newName     New name
     * @param array          $attributes  Additional attributes
     */
    public function __construct($trait, Name $method, $newModifier, $newName, array $attributes = array()) {
        parent::__construct(
            array(
                'trait'       => $trait,
                'method'      => $method,
                'newModifier' => $newModifier,
                'newName'     => $newName,
            ),
            $attributes
        );
    }
}
