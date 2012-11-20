<?php
require_once "GetCategoryRecommendationResult.php";
require_once "GetCategoryRecommendationParam.php";
require_once "GetFilteredRecommendationRequest.php";

 
/**
 * Class for executing a getCategoryRecommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetCategoryRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetCategoryRecommendationParam $param this query parameter object
     */
    function __construct( GetCategoryRecommendationParam $param )
    {
        parent::__construct( $param);
    }

    /**
     * {@inheritdoc}
     */
    protected function getServletName()
    {
        return "GetCategoryRecommendations_MainFrame";
    }

    /**
     * {@inheritdoc}
     */
    protected function createResponseObject()
    {
        return new GetCategoryRecommendationResult();
    }
}
