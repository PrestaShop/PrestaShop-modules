<?php
require_once "RegisterUserResult.php";
require_once "RegisterUserParam.php";
require_once "RequestTemplate.php";
require_once "RegisterUserResultHandler.php";


/**
 * Class for executing a registerUser query. 
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class RegisterUserRequest extends RequestTemplate
{

    /**
     * Constructs a new request
     * @param RegisterUserParam $param this query parameter object
     */
    function __construct( RegisterUserParam $param )
    {
        parent::__construct( $param);
    }

    
    /**
     * Creates a key value array of the parameters that need to be passed by  url.
     * @return array A key value map.
     */
    protected function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //add user id...
        $argMap["userID"] = $this->parameter->GetUserId();

        return $argMap;
    }


    /**
     * Creates a result object of appropriate type for this request.
     * @return RegisterUserResult A RegisterUserResult object.
     */
    protected function createResponseObject()
    {
        return new RegisterUserResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "RegisterUserServlet_MainFrame";
    }


    /**
     * Gets an appropriate result handler for this request.
     * @return RegisterUserResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new RegisterUserResultHandler();
    }

}

