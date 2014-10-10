<?php

require_once 'RequestParamBase.php';
require_once "Utils.php";

/**
 * Parameter class for get Advertisement queries..
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetAdvertisementParam extends RequestParamBase
{

    protected $userId = "";
    protected $profileMapId = -1;
    protected $pageId = 0;
    protected $languageCode = "";

    protected $conditions = array();


    /**
     * Gets the 2 characters ISO 639-1 language code.
     * @return string the language code
     */
    public function getLanguageCode() {
        return $this->languageCode;
    }

    /**
     * Sets the 2 characters ISO 639-1 language code of the current user.
     * @param string $languageCode the language code
     */
    public function setLanguageCode($languageCode) {
        $this->languageCode = $languageCode;
    }

    /**
     * Gets the profile ID.
     * @return integer the profile identifier
     */
    public function getProfileMapId() {
        return $this->profileMapId;
    }

    /**
     * Sets the profile ID. If your shop has many of them.
     * @param integer $profileId the profile identifier.
     */
    public function setProfileMapId($profileId) {
        $this->profileMapId = $profileId;
    }

    /**
     * Gets the current user identifier
     * @return string the user identifier
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Sets the user identifier (if he's logged in)
     * @param string $userId user identifier
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }


    /**
     * Return the page identifier, defined in prediggo
     * @return int
     */
    public function getPageId() {
        return $this->pageId;
    }

    /**
     * Sets the page identifier, ID musr be defined in prediggo backoffice
     * @param int $pageId
     */
    public function setPageId($pageId) {
        $this->pageId = $pageId;
    }


    /**
     * Gets the list of restrictions. Should be considered read only.
     * Restrictions are made of Key/Value pairs representing the name of the restricted attribute and the desired matching value.
     * @return array A set of conditions (Pair of attribute name and value)
     */
    public function getConditions() {
        return $this->conditions;
    }


    /**
     * Adds a new condition to the filter (attribute == value).
     * @param string $attributeName The name of the attribute which you want to filter.
     * @param string $matchingValue The desired matching value.
     */
    public function addCondition( $attributeName, $matchingValue ) {
        Utils::addPairToUniqueArray($this->conditions, $attributeName, $matchingValue);
    }

    /**
     * Adds a range condition to the filter ( min <= value <=  max).
     * @param string $attributeName The name of the attribute which you want to filter.
     * @param string $minValue The minimum value
     * @param string $maxValue The maximum value
     * @param boolean $minInclusive true if values that are equal to the min boundary are accepted
     * @param boolean $maxInclusive true if value that are equal to the max boundary are accepted
     */
    public function addConditionRange( $attributeName, $minValue, $maxValue, $minInclusive = true, $maxInclusive = true ) {
        $filter = $minValue . ','. $maxValue;

        $filter = ($minInclusive ? '[' : '(')  . $filter  ;
        $filter = $filter . ($maxInclusive ? ']' : ')')  ;

        Utils::addPairToUniqueArray($this->conditions, $attributeName, $filter);
    }
}

