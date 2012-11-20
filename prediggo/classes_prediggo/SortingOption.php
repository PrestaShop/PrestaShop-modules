<?php


/**
 * Description of a sorting option for ordering results on a search page.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class SortingOption
{
    private $clause;
    private $searchRefiningOption;

    /**
     * Get the search option that can be used in a parameter object to reconduct the search using the sorting clause described by this object.
     * @return string the search option to use in a GetXXXRecommendationParam object.
     */
    public function getSearchRefiningOption()
    {
        return $this->searchRefiningOption;
    }

    /**
     * Set the search option that can be use in a parameter object to reconduct the search using the sorting clause described by this object.
     * @param string $searchRefiningOption the search option to use in a GetXXXRecommendationParam object.
     */
    public function setSearchRefiningOption($searchRefiningOption)
    {
        $this->searchRefiningOption = $searchRefiningOption;
    }


    /**
     * Gets the sorting clause, see SortingClause.php for possible values
     * @see SortingClause
     * @return string The sorting clause
     */
    public function getClause()
    {
        return $this->clause;
    }

    /**
     * Sets the sorting clause, see SortingClause.php for possible values
     * @see SortingClause
     * @param string $clause The sorting clause
     */
    public function setClause( $clause)
    {
        $this->clause = $clause;
    }
}

