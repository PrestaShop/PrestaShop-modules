<?php

require_once "AutoCompleteResult.php";
require_once "AutoCompleteResultHandler.php";
require_once "AutoCompleteParam.php";
require_once "RequestTemplate.php";


/**
 * Autocomplete request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class AutoCompleteRequest  extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param AutoCompleteParam $param this query parameter object
     */
    function __construct( AutoCompleteParam $param )
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

        //autocomplete parameters
        $this->addParameterToMap($argMap, "languageCode", $this->parameter->getLanguageCode());
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "queryString", $this->parameter->getInputQuery());

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return AutoCompleteResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new AutoCompleteResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return AutoCompleteResult An AutoCompleteResult object.
     */
    protected function createResponseObject()
    {
        return new AutoCompleteResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "AutoCompleteServlet_MainFrame";
    }


}

