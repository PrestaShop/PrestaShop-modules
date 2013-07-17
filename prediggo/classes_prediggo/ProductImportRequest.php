<?php

require_once "ProductImportResult.php";
require_once "ProductImportResultHandler.php";
require_once "ProductImportParam.php";
require_once "RequestTemplate.php";


/**
 * ProductImport request handler
 *
 * @package prediggo4php
 * @subpackage requests
 * 
 * @author Stef
 */
class ProductImportRequest extends RequestTemplate
{
    

    /**
     * Constructs a new request
     * @param ProductImportParam $param this query parameter object
     */
    function __construct( ProductImportParam $param )
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

        //ProductImport parameters
        //$this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId() );
        $this->addParameterToMap($argMap, "job", "DATA_UPDATE");
        $this->addParameterToMap($argMap, "tokenid", $this->parameter->getTransactionId() );


        foreach( $this->parameter->getUpdates() as $entry  )
        {
            $this->addParameterToMap($argMap, "idandattributes", implode( "_/_", $entry)  );
        }

        return $argMap;
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return ProductImportResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new ProductImportResultHandler();
    }

    /**
     * Creates a result object of appropriate type for this request.
     * @return ProductImportResult An ProductImportResult object.
     */
    protected function createResponseObject()
    {
        return new ProductImportResult();
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

