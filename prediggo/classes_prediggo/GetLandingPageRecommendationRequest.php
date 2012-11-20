<?php
require_once "GetLandingPageRecommendationResult.php";
require_once "GetSearchRecommendationBaseResultHandler.php";
require_once "GetLandingPageRecommendationParam.php";
require_once "GetFilteredRecommendationRequest.php";


/**
 * Class for executing a getLandingPageRecommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetLandingPageRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetLandingPageRecommendationParam $param this query parameter object
     */
    function __construct( GetLandingPageRecommendationParam $param )
    {
        parent::__construct( $param);
    }


    /**
     * {@inheritdoc}
     */
    protected function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //no change.
        

        return $argMap;
    }

    /**
     * {@inheritdoc}
     */
    protected function getServletName()
    {
        return "GetLandingPageRecommendations_MainFrame";
    }

    /**
     * Gets an appropriate result handler for this request.
     * @return GetSearchRecommendationBaseResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetSearchRecommendationBaseResultHandler();
    }

    /**
     * {@inheritdoc}
     */
    protected function createResponseObject()
    {
        return new GetLandingPageRecommendationResult();
    }
}
