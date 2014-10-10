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
    protected $transactionId = "";
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
     * @param string $userId item identifier
     * @param string $attName the attribute name eg: "manufacturer"
     * @param string $attValue the attribute value eg: "apple"
     */
    public function addUpdate($userId, $attName, $attValue) {

        $this->updates[] = array( $userId, $attName, $attValue);


    }

    /**
     * Gets the transaction ID received when beginning the transaction
     * @param string $transactionId
     *
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * Sets the transaction ID received when beginning the transaction
     * @return string The transaction ID
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }


}

