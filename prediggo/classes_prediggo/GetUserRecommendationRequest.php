<?php
require_once "GetFilteredRecommendationRequest.php";
require_once "GetUserRecommendationResult.php";
require_once "GetUserRecommendationParam.php";


/**
 * Class for executing a getUserRecommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetUserRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetUserRecommendationParam $param this query parameter object
     */
    function __construct( GetUserRecommendationParam $param )
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

        //add method id...
        $argMap["methodID"] = $this->parameter->GetRecommendationMethodToUse();

        return $argMap;
    }


    /**
     * Creates a result object of appropriate type for this request.
     * @return GetUserRecommendationResult A GetUserRecommendationResult object.
     */
    protected function createResponseObject()
    {
        return new GetUserRecommendationResult();
    }



    /**
     * Gets the name of the servlet which serves this kind of request on prediggo side.
     * @return string The name of the servlet
     */
    protected function getServletName()
    {
        return "GetUserRecommendationsServlet_MainFrame";
    }


}

