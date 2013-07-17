<?php
require_once "GetFilteredRecommendationRequest.php";
require_once "GetItemRecommendationResult.php";
require_once "GetItemRecommendationParam.php";
require_once "GetItemRecommendationResultHandler.php";


/**
 * Class for executing a getItemRecommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetItemRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetItemRecommendationParam $param this query parameter object
     */
    function __construct( GetItemRecommendationParam $param )
    {
        parent::__construct( $param);
    }



    /**
     * Gets an appropriate result handler for this request.
     * @return GetItemRecommendationResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetItemRecommendationResultHandler();
    }
    

    /**
     * Creates a key value array of the parameters that need to be passed by  url.
     * @return array A key value map.
     */
    protected  function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //item infos
        $this->addParameterToMap($argMap, "itemID", $this->parameter->getItemInfo()->getItemId());
        $this->addParameterToMap($argMap, "name", $this->parameter->getItemInfo()->getItemName());


        $bufferKeys = "";
        $bufferValues = "";

        //implode the key values pairs into separate strings
        Utils::implodeKeyValuePairsToSeparatedString( $this->parameter->getItemInfo()->getAttributes(), "_/_",  $bufferKeys, $bufferValues);

        //add parameters
        $this->addParameterToMap($argMap, "itemInfoAttributeNames", $bufferKeys);
        $this->addParameterToMap($argMap, "itemInfoAttributeValues", $bufferValues);

        return $argMap;
    }



    /**
     * Creates a result object of appropriate type for this request.
     * @return GetItemRecommendationResult A GetItemRecommendationResult object.
     */
    protected function createResponseObject()
    {
        return new GetItemRecommendationResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "GetItemRecommandation_MainFrame";
    }


    

}
