<?php

require_once "GetFilteredRecommendationParam.php";

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

}

