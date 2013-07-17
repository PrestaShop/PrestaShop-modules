<?php

require_once "RequestTemplate.php";
require_once "Utils.php";



/**
 * Parent parameter class for filtered recommendation queries.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
abstract class GetFilteredRecommendationParam extends RequestParamBase
{

    protected $languageCode = "";
    protected $profileMapId = -1;
    protected $userId = "";
    protected $nbRecommendation = 4 ;
    protected $refererUrl = "";
    
    protected $showAds = false ;
    
    protected $conditions = array();

    
    


    /**
     * Gets the referer url
     * @return string the referer Url
     */
    public function getRefererUrl()
    {
        return $this->refererUrl;
    }


    /**
     * Sets the referer Url.
     * @param string refererUrl the referer Url
     */
    public function setRefererUrl( $refererUrl)
    {
        $this->refererUrl = $refererUrl;
    }

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
     * Gets the current user identifier
     * @return string the user identifier
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the user identifier (if he's logged in)
     * @param string $userId user identifier
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


 
    /**
     * Gets the number of recommendations you will receive from this query.
     * @return integer the number of recommendation
     */
    public function getNbRecommendation()
    {
        return $this->nbRecommendation;
    }

    /**
     * Sets the number of recommendations the prediggo should generate.
     * @param integer $nbRecommendation the number of recommendations you want
     */
    public function setNbRecommendation($nbRecommendation)
    {
        $this->nbRecommendation = $nbRecommendation;
    }

    /**
     * Tell if ads should be added in the query result.
     * @return boolean true/false
     */
    public function getShowAds()
    {
        return $this->showAds;
    }

    /**
     * Include ads in the results. DO NOT enable this without prior discussion with prediggo.
     * @param boolean $showAds true = include ads in query result.
     */
    public function setShowAds($showAds)
    {
        $this->showAds = $showAds;
    }


    /**
     * @deprecated
     *
     * Optional parameter, use it only if you need to limit the recommendation
     * to specific categories.
     * @param string $categoryName the name of a category
     */
    public function addRecommendationCategory( $categoryName )
    {
        trigger_error('Use conditions instead', E_USER_DEPRECATED);
        $this->addCondition("genre", $categoryName);
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
     * Gets the list of restrictions. Should be considered read only.
     * Restrictions are made of Key/Value pairs representing the name of the restricted attribute and the desired matching value.
     * @return array A set of conditions (Pair of attribute name and value)
     */
    public function getConditions()
    {
        return $this->conditions;
    }


    /**
     * Adds a new condition to the filter (attribute == value).
     * @param string $attributeName The name of the attribute which you want to filter.
     * @param string $matchingValue The desired matching value.
     */
    public function addCondition( $attributeName, $matchingValue )
    {
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
    public function addConditionRange( $attributeName, $minValue, $maxValue, $minInclusive = true, $maxInclusive = true )
    {
        $filter = $minValue . ','. $maxValue;

        $filter = ($minInclusive ? '[' : '(')  . $filter  ;
        $filter = $filter . ($maxInclusive ? ']' : ')')  ;

        Utils::addPairToUniqueArray($this->conditions, $attributeName, $filter);
    }


}

