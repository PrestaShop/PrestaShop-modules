<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for beginUserProfileImport queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class BeginUserProfileImportParam extends RequestParamBase
{

    protected $profileMapId = -1;

    /**
     * Gets the profile ID.
     * @return integer the profile identifier
     */
    public function getProfileMapId()
    {
        return $this->profileMapId;
    }

    /**
     * Sets the profile ID. If your shop has many of them.
     * @param integer $profileId the profile identifier.
     */
    public function setProfileMapId($profileId)
    {
        $this->profileMapId = $profileId;
    }



}

