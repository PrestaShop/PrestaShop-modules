<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for ProductImport queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ProductImportParam extends RequestParamBase
{

    protected $updates = array();

    /**
     * Return the list of update
     * @return array A 2D array of values
     */
    public function getUpdates()
    {
        return $this->updates;
    }

    /**
     * @param string $itemId item identifier
     * @param string $attName the attribute name eg: "manufacturer"
     * @param string $attValue the attribute value eg: "apple"
     * @param float $score (default is null -> unspecified)
     */
    public function addUpdate($itemId, $attName, $attValue, $score = null ) {

        if( $score == null)
            $this->updates[] = array( $itemId, $attName, $attValue);
        else
            $this->updates[] = array( $itemId, $attName, $attValue, $score);

    }


}

