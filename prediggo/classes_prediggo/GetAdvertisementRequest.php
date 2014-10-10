<?php

require_once "GetAdvertisementResult.php";
require_once "GetAdvertisementResultHandler.php";
require_once "GetAdvertisementParam.php";
require_once "RequestTemplate.php";


/**
 * GetAdvertisement request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class GetAdvertisementRequest  extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param GetAdvertisementParam $param this query parameter object
     */
    function __construct( GetAdvertisementParam $param )
    {
        parent::__construct( $param);
    }


    /**
     * Creates a key value array of the parameters that need to be passed by url.
     * @return array A key value map.
     */
    protected  function getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //parameters
        $this->addParameterToMap($argMap, "userID", $this->parameter->getUserId());
        $this->addParameterToMap($argMap, "languageCode", $this->parameter->getLanguageCode());
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "pageID", $this->parameter->getPageId() );


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
     * Creates a result object of appropriate type for this request.
     * @return GetAdvertisementResult A result object.
     */
    protected function createResponseObject()
    {
        return new GetAdvertisementResult();
    }



    /**
     * Gets an appropriate result handler for this request.
     * @return GetAdvertisementResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetAdvertisementResultHandler();
    }




    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return  "/mock/GetAdvertisement";
    }


}

