<?php

require_once "GetFilteredRecommendationRequest.php";
require_once "GetSearchPageRecommendationResultHandler.php";
require_once "GetSearchPageRecommendationParam.php";
require_once "GetSearchPageRecommendationResult.php";




/**
 * Class for executing a getSearchPageRecommendation query.
 *
 * @package prediggo4php
 * @subpackage requests
 *
 * @author Stef
 */
class GetSearchPageRecommendationRequest extends GetFilteredRecommendationRequest
{

    /**
     * Constructs a new request
     * @param GetSearchPageRecommendationParam $param this query parameter object
     */
    function __construct( GetSearchPageRecommendationParam $param )
    {
        parent::__construct( $param);
    }


    /**
     * {@inheritdoc}
     */
    protected function  getArgumentMap()
    {
        $argMap = parent::getArgumentMap();

        //add query specific..
        $this->addParameterToMap($argMap, "queryString", $this->parameter->getSearchString());
        $this->addParameterToMap($argMap, "searchRefiningOptions", $this->parameter->getSearchRefiningOption());

        $this->addParameterToMap($argMap, "pageNb", $this->parameter->getPageNumber());
        $this->addParameterToMap($argMap, "nbResultsPerPage", $this->parameter->getMaxNbResultsPerPage());
        $this->addParameterToMap($argMap, "methodID", $this->parameter->getSortingOrder());

        return $argMap;
    }

    /**
     * {@inheritdoc}
     */
    protected function getServletName()
    {
        return "GetSearchPageRecommendations_MainFrame";
    }

    /**
     * Gets an appropriate result handler for this request.
     * @return GetSearchRecommendationBaseResultHandler An appropriate result handler for this request.
     */
    protected function getResultHandler()
    {
        return new GetSearchPageRecommendationResultHandler();
    }

    /**
     * {@inheritdoc}
     */
    protected function createResponseObject()
    {
        return new GetSearchPageRecommendationResult();
    }

}

