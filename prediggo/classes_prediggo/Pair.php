<?php


/**
 * This class represents a simple pair of objets, exact types are defined at construction time.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class Pair
{

     protected  $first;
     protected  $second;

    /**
     * Constructs a new pair
     * @param mixed $first the first element
     * @param mixed $second the second element
     */
    function __construct($first, $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    /**
     * Gets the first element of this pair
     * @return mixed the first element of this pair
     */
    public function getFirst() { return $this->first; }


    /**
     * Gets the second element of this pair
     * @return mixed the second element of this pair
     */
    public function getSecond() { return $this->second; }


}
