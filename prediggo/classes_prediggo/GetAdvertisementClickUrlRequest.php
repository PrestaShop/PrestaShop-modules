<?php

require_once "GetAdvertisementClickUrlResult.php";
require_once "GetAdvertisementClickUrlResultHandler.php";
require_once "GetAdvertisementClickUrlParam.php";
require_once "RequestTemplate.php";


/**
 * GetAdvertisementClickUrl request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class GetAdvertisementClickUrlRequest  extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param GetAdvertisementClickUrlParam $param this query parameter object
     */
    function __construct( GetAdvertisementClickUrlParam $param )
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
        $this->addParameterToMap($argMap, "clickID", $this->parameter->getClickID());

        return $argMap;
    }




    /**
     * Creates a result object of appropriate type for this request.
     * @return GetAdvertisementClickUrlResult A result object.
     */
    protected function createResponseObject()
    {
        return new GetAdvertisementClickUrlResult();
    }



    /**
     * Gets an appropriate result handler for this request.
     * @return GetAdvertisementClickUrlResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetAdvertisementClickUrlResultHandler();
    }




    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return  "/mock/GetAdvertisementClickID";
    }


}

