<?php

require_once "BeginUserProfileImportResult.php";
require_once "BeginUserProfileImportResultHandler.php";
require_once "BeginUserProfileImportParam.php";
require_once "RequestTemplate.php";


/**
 * BeginUserProfileImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class BeginUserProfileImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param BeginUserProfileImportParam $param this query parameter object
     */
    function __construct( BeginUserProfileImportParam $param )
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

        //UserProfileImport parameters
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "USER_PROFILE_UPDATE");
        $this->addParameterToMap($argMap, "action", "START");

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return BeginUserProfileImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new BeginUserProfileImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return BeginUserProfileImportResult A BeginUserProfileImportResult object.
     */
    protected function createResponseObject()
    {
        return new BeginUserProfileImportResult();
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

