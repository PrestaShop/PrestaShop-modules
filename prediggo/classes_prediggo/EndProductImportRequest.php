<?php

require_once "EndProductImportResult.php";
require_once "EndProductImportResultHandler.php";
require_once "EndProductImportParam.php";
require_once "RequestTemplate.php";


/**
 * EndProductImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class EndProductImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param EndProductImportParam $param this query parameter object
     */
    function __construct( EndProductImportParam $param )
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

        //EndProductImport parameters
        //$this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "DATA_UPDATE");
        $this->addParameterToMap($argMap, "action", $this->parameter->getCommitMode());
        $this->addParameterToMap($argMap, "tokenid", $this->parameter->getTransactionId() );

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return EndProductImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new EndProductImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return EndProductImportResult An EndProductImportResult object.
     */
    protected function createResponseObject()
    {
        return new EndProductImportResult();
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

