<?php


require_once "Utils.php";
require_once "RequestTemplate.php";
require_once "GetFilteredRecommendationParam.php";



/**
 * Base class for executing a filtered recommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
abstract class GetFilteredRecommendationRequest extends RequestTemplate
{

    /**
     * Constructs a new request
     * @param GetItemRecommendationParam $param this query parameter object
     */
    function __construct( GetFilteredRecommendationParam $param )
    {
        parent::__construct( $param);
    }



    /**
     * Creates a key value array of the parameters that need to be passed by url.
     * @return array A key value map.
     */
    protected function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //common paramters for all getXXXRecommendation style queries...
        $argMap["nbRec"] = $this->parameter->getNbRecommendation() ;
        $argMap["showAds"] = $this->parameter->getShowAds() ? "true" : "false" ;
        $argMap["userID"] = $this->parameter->getUserId();
        $argMap["classID"] = $this->parameter->getProfileMapId();
        $argMap["languageCode"] = $this->parameter->getLanguageCode();
        $argMap["referURL"] = $this->parameter->getRefererUrl();
        
        $bufferKeys = "";
        $bufferValues = "";

        //implode the key values pairs into separate strings
        Utils::implodeKeyValuePairsToSeparatedString( $this->parameter->getConditions(), "_/_",  $bufferKeys, $bufferValues);

        //add parameters
        $argMap["attributeNames"] = $bufferKeys;
        $argMap["attributeValues"] = $bufferValues;

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return GetRecommendationResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetRecommendationResultHandler();
    }

}
