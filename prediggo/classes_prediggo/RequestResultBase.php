<?php

/**
 * Base class of all request result classes.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class RequestResultBase
{
    protected $timeInMs = 0.0;
    protected $sessionId = "";
    protected $status = 0;
    protected $statusMessage = "";



    /**
     * Gets the session id that was given in parameter to the query.
     * @return string the session id
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Sets the session id.
     * @param string $sessionId the session id to set.
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    /**
     * Gets the status code of this query.
     * @return integer the status code
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Sets the status code.
     * @param integer $status the status code to set.
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Gets the message corresponding to the status code.
     * @return string the status message.
     */
    public function getStatusMessage() {
        return $this->statusMessage;
    }

    /**
     * Sets the status message.
     * @param string $statusMessage the status message to set
     */
    public function setStatusMessage($statusMessage) {
        $this->statusMessage = $statusMessage;
    }


        /**
     * Gets the time that was needed to compute these recommendations on prediggo side.
     * @return float the time in milliseconds
     */
    public function getTimeInMs() {
        return $this->timeInMs;
    }

    /**
     * Sets the time that was needed to compute these recommendations on prediggo side.
     * @param float $timeInMs the time in milliseconds
     */
    public function setTimeInMs($timeInMs) {
        $this->timeInMs = $timeInMs;
    }


}

