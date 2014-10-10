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
    protected $transactionId = "";

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

