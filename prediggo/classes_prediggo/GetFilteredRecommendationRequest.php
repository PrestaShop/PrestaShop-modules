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
     * @param GetFilteredRecommendationParam $param this query parameter object
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
        $this->addParameterToMap($argMap, "nbRec", $this->parameter->getNbRecommendation() );
        $this->addParameterToMap($argMap, "showAds", $this->parameter->getShowAds() ? "true" : "false" );
        $this->addParameterToMap($argMap, "userID", $this->parameter->getUserId());
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId());
        $this->addParameterToMap($argMap, "languageCode", $this->parameter->getLanguageCode());
        $this->addParameterToMap($argMap, "referURL", $this->parameter->getRefererUrl());
        
        $bufferKeys = "";
        $bufferValues = "";

        //implode the key values pairs into separate strings
        Utils::implodeKeyValuePairsToSeparatedString( $this->parameter->getConditions(), "_/_",  $bufferKeys, $bufferValues);

        //add parameters
        $this->addParameterToMap($argMap, "attributeNames", $bufferKeys);
        $this->addParameterToMap($argMap, "attributeValues", $bufferValues);

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
