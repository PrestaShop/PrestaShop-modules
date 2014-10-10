<?php

include_once "SortingClause.php";

/**
 * Search statistics, provide information about the search query. Can be used for setting up pagination (among others).
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author vschicke
 */
class SearchStatistics
{
    private $timeInMs = 0.0;
    private $totalSearchResults = 0;
    private $resultsInCurrentPage = 0;
    private $maxResultsPerPage = 0;
    private $currentPageNumber = 0;
    private $totalSearchPages = 0;
    private $userQuery = '';
    private $currentSorting = 0;
    private $languageCode = '';

    /**
     * Returns the server side duration of the query.
     * @return float the duration in milliseconds
     */
    public function getTimeInMs()
    {
        return $this->timeInMs;
    }

    /**
     * Set the server side duration of the query
     * @param float $timeInMs the duration in milliseconds
     */
    public function setTimeInMs( $timeInMs)
    {
        $this->timeInMs = $timeInMs;
    }

    /**
     * Returns the number of available search results for this query
     * @return integer The total number of matches
     */
    public function getTotalSearchResults()
    {
        return $this->totalSearchResults;
    }

    /**
     * Sets the number of available search results for this query
     * @param integer $totalSearchResults The total number of matches
     */
    public function setTotalSearchResults($totalSearchResults)
    {
        $this->totalSearchResults = $totalSearchResults;
    }

    /**
     * Returns the number of available search results in the current page
     * @return integer The number of available results in the current page
     */
    public function getResultsInCurrentPage()
    {
        return $this->resultsInCurrentPage;
    }

    /**
     * Sets the number of available search results in the current page
     * @param integer $resultsInCurrentPage The number of results
     */
    public function setResultsInCurrentPage( $resultsInCurrentPage)
    {
        $this->resultsInCurrentPage = $resultsInCurrentPage;
    }
    
    /**
     * Gets the maximum number of search results displayed per page, by default the value returned is configured in the backoffice
     * @return integer The number of results per page
     */
    public function getMaxResultsPerPage()
    {
        return $this->maxResultsPerPage;
    }

    /**
     * Sets the maximum number of search results displayed per page, by default the value returned is configured in the backoffice
     * @param integer $resultsPerPage The number of results per page
     */
    public function setMaxResultsPerPage( $resultsPerPage)
    {
        $this->maxResultsPerPage = $resultsPerPage;
    }
    

    /**
     * Returns the currently active page number
     * @return the currently active page number
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPageNumber;
    }

    /**
     * Sets the currently active page number
     * @param integer $currentPageNumber the page number
     */
    public function setCurrentPageNumber($currentPageNumber)
    {
        $this->currentPageNumber = $currentPageNumber;
    }

    /**
     * Returns the total number of search pages for the active resultset
     * @return integer the number of search pages
     */
    public function getTotalSearchPages()
    {
        return $this->totalSearchPages;
    }

    /**
     * Sets the total number of search pages
     * @param integer $totalSearchPages the number of search pages
     */
    public function setTotalSearchPages( $totalSearchPages)
    {
        $this->totalSearchPages = $totalSearchPages;
    }

     /**
     * Returns the researched string
     * @return string the researched string
     */
    public function getUserQuery()
    {
        return $this->userQuery;
    }

    /**
     * Sets the researched string
     * @param string $userQuery the researched string
     */
    public function setUserQuery( $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * Returns the current language code
     * @return string the language code
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Sets the language code
     * @param string languageCode the language code
     */
    public function setLanguageCode( $languageCode)
    {
        $this->languageCode = $languageCode;
    }

     /**
     * Returns the currently active sorting
     * @return int the currently active sorting, see SortingClause.php for possible values
     * @see SortingClause
     */
    public function getCurrentSorting()
    {
        return $this->currentSorting;
    }

    /**
     * Sets the currently active sorting
     * @param int $sortingClause the currently active sorting, see SortingClause.php for possible values
     * @see SortingClause
     */
    public function setCurrentSorting( $sortingClause )
    {
        $this->currentSorting = $sortingClause;
    }
}