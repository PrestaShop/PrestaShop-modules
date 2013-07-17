<?php

require_once "BeginProductImportResult.php";
require_once "BeginProductImportResultHandler.php";
require_once "BeginProductImportParam.php";
require_once "RequestTemplate.php";


/**
 * BeginProductImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class BeginProductImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param BeginProductImportParam $param this query parameter object
     */
    function __construct( BeginProductImportParam $param )
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

        //BeginProductImport parameters
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "DATA_UPDATE");
        $this->addParameterToMap($argMap, "action", "START");

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return BeginProductImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new BeginProductImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return BeginProductImportResult An BeginProductImportResult object.
     */
    protected function createResponseObject()
    {
        return new BeginProductImportResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "APIImportProcessServlet_MainFrame";
    }


}

