<?php

namespace PhpParser\Node\Stmt;

use PhpParser\Node;
use PhpParser\Error;
use PhpParser\Node\Name;

/**
 * @property Name $name  Namespace/Class to alias
 * @property Name $alias Alias
 */
class UseUse extends Node\Stmt
{
    /**
     * Constructs an alias (use) node.
     *
     * @param Name        $name       Namespace/Class to alias
     * @param Name        $alias      Alias
     * @param array       $attributes Additional attributes
     */
    public function __construct(Name $name, Name $alias, array $attributes = array()) {
        if ("" === $alias->getLast()) {
            $alias->set($name->getLast());
        }

        if ('self' == $alias || 'parent' == $alias) {
            throw new Error(sprintf(
                'Cannot use %s as %s because \'%2$s\' is a special class name',
                $name, $alias
            ));
        }

        parent::__construct(
            array(
                'name'  => $name,
                'alias' => $alias,
            ),
            $attributes
        );
    }
}
