<?php

require_once "GetFilteredRecommendationParam.php";
include_once "SortingClause.php";

/**
 * Parameter class for GetSearchPageRecommendation queries.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetSearchPageRecommendationParam extends GetFilteredRecommendationParam
{

    private $searchRefiningOption = "";
    private $searchString = "";
    private $maxNbResultsPerPage = 0;
    private $pageNumber = 0;
    private $sortingOrder = -1;


    /**
     * The max number of items per page (0 = use backoffice value)
     *
     * @param int $maxNbResultsPerPage
     */
    public function setMaxNbResultsPerPage($maxNbResultsPerPage)
    {
        $this->maxNbResultsPerPage = $maxNbResultsPerPage;
    }

    /**
     * The max number of items per page (0 = use backoffice value)
     * @return int
     */
    public function getMaxNbResultsPerPage()
    {
        return $this->maxNbResultsPerPage;
    }


    /**
     * The initial page number to return (default is 0 = not set)
     * @param int $pageNumber The desired page
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * The configured page number (default is 0 = not set)
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }



    /**
     * Get the search option that can be used in a parameter object to continue or extend a previous search.
     * @return string the search option used to refine the query.
     */
    public function getSearchRefiningOption()
    {
        return $this->searchRefiningOption;
    }

    /**
     * Set the search option (ie: next page / sorting / filter ) retrieved in some previous query to make this request a *refinement* of a previous search.
     * If this property is not empty, the query engine might ignore most other parameters.
     *
     * @param string $searchRefiningOption the search option used to refine the query.
     */
    public function setSearchRefiningOption($searchRefiningOption)
    {
        $this->searchRefiningOption = $searchRefiningOption;
    }


    /**
     * Gets the search String typed by the user
     * @return string the search string
     */
    public function getSearchString()
    {
        return $this->searchString;
    }

    /**
     * Sets the search String typed by the user
     * @param string $searchString the search string
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }


    /**
     * Gets the initial sorting clause, see SortingClause.php for possible values  (-1 = backoffice default)
     * @see SortingClause
     * @param int $sortingOrder The sorting clause
     */
    public function setSortingOrder($sortingOrder)
    {
        $this->sortingOrder = $sortingOrder;
    }


    /**
     * Sets the initial sorting clause, see SortingClause.php for possible values (-1 = backoffice default)
     * @see SortingClause
     * @return int The sorting clause
     */
    public function getSortingOrder()
    {
        return $this->sortingOrder;
    }



}

