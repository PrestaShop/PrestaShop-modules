<?php

require_once "UserProfileImportResult.php";
require_once "UserProfileImportResultHandler.php";
require_once "UserProfileImportParam.php";
require_once "RequestTemplate.php";


/**
 * UserProfileImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class UserProfileImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param UserProfileImportParam $param this query parameter object
     */
    function __construct( UserProfileImportParam $param )
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
        //$this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "USER_PROFILE_UPDATE");
        $this->addParameterToMap($argMap, "tokenid", $this->parameter->getTransactionId() );


        foreach( $this->parameter->getUpdates() as $entry  )
        {
            $this->addParameterToMap($argMap, "idandattributes", implode( "_/_", $entry)  );
        }

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return UserProfileImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new UserProfileImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return UserProfileImportResult An UserProfileImportResult object.
     */
    protected function createResponseObject()
    {
        return new UserProfileImportResult();
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

