<?php

require_once "EndUserProfileImportResult.php";
require_once "EndUserProfileImportResultHandler.php";
require_once "EndUserProfileImportParam.php";
require_once "RequestTemplate.php";


/**
 * EndUserProfileImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class EndUserProfileImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param EndUserProfileImportParam $param this query parameter object
     */
    function __construct( EndUserProfileImportParam $param )
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

        //EndUserProfileImport parameters
        //$this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "USER_PROFILE_UPDATE");
        $this->addParameterToMap($argMap, "action", $this->parameter->getCommitMode());
        $this->addParameterToMap($argMap, "tokenid", $this->parameter->getTransactionId() );

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return EndUserProfileImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new EndUserProfileImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return EndUserProfileImportResult An EndUserProfileImportResult object.
     */
    protected function createResponseObject()
    {
        return new EndUserProfileImportResult();
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

