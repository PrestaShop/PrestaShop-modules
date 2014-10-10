<?php

require_once "RequestParamBase.php";
require_once "ImportCommitMode.php";


/**
 * Result class for EndProductImport queries
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class EndProductImportParam extends RequestParamBase
{
    protected $transactionId = "";
    protected $commitMode = ImportCommitMode::COMMIT_IN_MEMORY;

    /**
     * Sets the commit mode, default is COMMIT_IN_RAM_ONLY
     * @param string $commitMode
     * @see ImportCommitMode
     */
    public function setCommitMode($commitMode)
    {
        $this->commitMode = $commitMode;
    }

    /**
     * Gets the commit mode
     * @return string
     * @see ImportCommitMode
     */
    public function getCommitMode()
    {
        return $this->commitMode;
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

