<?php
require_once "GetTopNSalesParam.php";
require_once "GetTopNSalesResult.php";
require_once "GetFilteredRecommendationRequest.php";



/**
 * Class for executing a getTopNSales query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetTopNSalesRequest extends GetFilteredRecommendationRequest
{

    /**
     ** Constructs a new request
     * @param GetTopNSalesParam $param this query parameter object
     */
    function __construct( GetTopNSalesParam $param )
    {
        parent::__construct( $param);
    }

    
    /**
     * Creates a result object of appropriate type for this request.
     * @return GetTopNSalesResult A GetTopNSalesResult object.
     */
    protected function createResponseObject()
    {
        return new GetTopNSalesResult();
    }


    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "GetTopNSalesServlet_MainFrame";
    }

}

