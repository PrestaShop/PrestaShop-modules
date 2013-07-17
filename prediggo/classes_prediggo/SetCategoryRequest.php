<?php
require_once "SetCategoryResult.php";
require_once "SetCategoryParam.php";
require_once "RequestTemplate.php";


/**
 * Class for executing a setCategory query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class SetCategoryRequest extends RequestTemplate
{

    /**
     * Constructs a new request
     * @param SetCategoryParam $param this query parameter object
     */
    function __construct( SetCategoryParam $param )
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

        //add category and profile id
        $this->addParameterToMap($argMap, "category", $this->parameter->getCategory());
        $this->addParameterToMap($argMap, "classID", $this->parameter->getProfileMapId());

        return $argMap;
    }


    /**
     * Creates a result object of appropriate type for this request.
     * @return SetCategoryResult A SetCategoryResult object.
     */
    protected function createResponseObject()
    {
        return new SetCategoryResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "SetCategoryServlet_MainFrame";
    }

}
