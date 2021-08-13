<?php

namespace KTPL\AkeneoTrashBundle\Factory;

use KTPL\AkeneoTrashBundle\Entity\AkeneoTrash;

/**
 * Akeneo trash factory
 */
class AkeneoTrashFactory
{
    /** @var string */
    protected $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Create a version
     *
     * @return AkeneoTrash
     */
    public function create()
    {
        return new $this->class();
    }
}
