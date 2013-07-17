<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for UserProfileImport queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class UserProfileImportParam extends RequestParamBase
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
     */
    public function addUpdate($itemId, $attName, $attValue) {

        $this->updates[] = array( $itemId, $attName, $attValue);


    }


}

