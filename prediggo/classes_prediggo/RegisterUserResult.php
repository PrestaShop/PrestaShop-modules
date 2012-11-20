<?php

require_once 'RequestResultBase.php';

/**
 * This class represents the result of a registerUser query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class RegisterUserResult extends RequestResultBase
{
    
    protected $userId;

    /**
     * Gets the user identifier that was used in the query.
     * @return string the user identifier
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the user identifier that was used in the query.
     * @param string $userId the user identifier
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


	
}

