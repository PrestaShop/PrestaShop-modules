<?php

require_once 'RequestParamBase.php';

/**
 * Parameter class for autoComplete queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class AutoCompleteParam extends RequestParamBase
{

    protected $profileMapId = -1;
    protected $inputString = "";
    protected $languageCode = "";

    /**
     * Gets the 2 characters ISO 639-1 language code.
     * @return string the language code
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Sets the 2 characters ISO 639-1 language code of the current user.
     * @param string $languageCode the language code
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }

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

   /**
     * Get the researched input string.
     *
     * @return string the researched input string
     */
    public function getInputQuery()
    {
        return $this->inputString;
    }

    /**
     * Set the researched input string
     *
     * @param string $inputString the researched input string
     */
    public function setInputQuery( $inputString)
    {
        $this->inputString = $inputString;
    }


}

