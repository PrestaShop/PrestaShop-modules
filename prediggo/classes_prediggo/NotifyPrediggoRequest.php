<?php
require_once "NotifyPrediggoResult.php";
require_once "NotifyPrediggoParam.php";
require_once "RequestTemplate.php";

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for executing a notifyPrediggo query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class NotifyPrediggoRequest extends RequestTemplate
{

    /**
     * Constructs a new request
     * @param NotifyPrediggoParam $param this query parameter object
     */
    function __construct( NotifyPrediggoParam $param )
    {
        parent::__construct( $param);
    }


    /**
     * Creates a key value array of the parameters that need to be passed by  url.
     * @return array A key value map.
     */
    protected  function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //add click parameter
        $argMap["clickparameters"] = $this->parameter->getNotificationId();

        return $argMap;
    }


    /**
     * Creates a result object of appropriate type for this request.
     * @return NotifyPrediggoResult A NotifyPrediggoResult object.
     */
    protected function createResponseObject()
    {
        return new NotifyPrediggoResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "StoreClickServlet_MainFrame";
    }

}

