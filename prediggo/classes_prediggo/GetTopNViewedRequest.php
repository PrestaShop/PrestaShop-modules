<?php
require_once "GetTopNViewedParam.php";
require_once "GetTopNViewedResult.php";
require_once "GetFilteredRecommendationRequest.php";



/**
 * Class for executing a getTopNViewed query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetTopNViewedRequest extends GetFilteredRecommendationRequest
{

    /**
     ** Constructs a new request
     * @param GetTopNViewedParam $param this query parameter object
     */
    function __construct( GetTopNViewedParam $param )
    {
        parent::__construct( $param);
    }

    
    /**
     * Creates a result object of appropriate type for this request.
     * @return GetTopNViewedResult A GetTopNViewedResult object.
     */
    protected function createResponseObject()
    {
        return new GetTopNViewedResult();
    }


    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "GetTopNViewedServlet_MainFrame";
    }

}

