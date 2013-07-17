<?php

require_once "RequestResultBase.php";


/**
 * Result class for EndProductImport queries
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class EndProductImportResult extends RequestResultBase
{
    protected $transactionId = "";

    /**
     *
     * Sets the transaction ID
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * Gets the transaction ID
     * @return string the transaction ID
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }





}

