<?php

require_once "GetBasketRecommendationResult.php";
require_once "GetFilteredRecommendationRequest.php";
require_once "GetBasketRecommendationParam.php";
require_once "Utils.php";

/**
 * Class for executing getBasketRecommendation queries.
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class GetBasketRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetBasketRecommendationParam $param this query parameter object
     */
    function __construct( GetBasketRecommendationParam $param )
    {
        parent::__construct( $param);
    }


    /**
     * {@inheritdoc}
     */
    protected function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        $bufferKeys = "";
        $bufferValues = "";

        //implode the key values pairs into separate strings
        Utils::implodeKeyValuePairsToSeparatedString( $this->parameter->getBasketItems(), "_/_",  $bufferKeys, $bufferValues);

        //add parameters
        $argMap["classIDs"] = $bufferKeys;
        $argMap["itemIDs"] = $bufferValues;
        
        return $argMap;
    }


    /**
     * {@inheritdoc}
     */
    protected function getServletName()
    {
        return "GetBasketRecommendations_MainFrame";
    }

     /**
     * {@inheritdoc}
     */
    protected function createResponseObject()
    {
        return new GetBasketRecommendationResult();
    }
}
