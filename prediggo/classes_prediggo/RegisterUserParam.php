<?php

require_once 'RequestParamBase.php';


/**
 * Parameter class for a registerUser query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class RegisterUserParam extends RequestParamBase
{


    protected $userId = '';

   
    /**
     * Get the current user identifier
     * @return string the user identifier
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user identifier to register (as defined in users.xml)
     * @param string $userId user identifier
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

}

