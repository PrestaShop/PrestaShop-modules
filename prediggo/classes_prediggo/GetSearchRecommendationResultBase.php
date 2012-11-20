<?php
require_once "ChangePageOption.php";
require_once "SearchWord.php";
require_once "GetRecommendationResultBase.php";
require_once "SearchStatistics.php";

/**
 * Base class of search based queries result, handle "did you mean" string and multiple profiles.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetSearchRecommendationResultBase extends GetRecommendationResultBase
{

    protected $didYouMeanWords = array();
    protected $changePageLinks = array();
    protected $searchStatistics;

    /**
     * Set the search statistics
     * @param SearchStatistics $stats The search statistics for this query
     */
    public function setSearchStatistics($stats)
    {
        $this->searchStatistics = $stats;

    }

    /**
     * Get the search statistics
     * @return SearchStatistics An object containing information about this query results and pagination
     */
    public function getSearchStatistics()
    {
        return $this->searchStatistics;
    }


    /**
     * Gets the "did you mean?" string, only available if there's no exact matches.
     * @return string a corrected search string, or empty if we don't have a proposal (never null).
     */
    public function didYouMean()
    {
        $buffer = "";

        foreach( $this->didYouMeanWords as $word   )
            $buffer .= $word->getWord()." " ;

        return trim($buffer);
    }

    /**
     * Gets the words forming the didYouMean string if available.
     * @return array A list of SearchWord objects, or an empty collection if we don't have a proposal..
     */
    public function getDidYouMeanWords()
    {
        return $this->didYouMeanWords;
    }

    /**
     * Adds a search word to this object. This function should not be used by customer code.
     * 
     * @param SearchWord $word The word to add.
     */
    public function addDidYouMeanWord( SearchWord  $word )
    {
        $this->didYouMeanWords[] = $word;
    }

    /**
     * Get the necessary information to request the previous / next page of results.
     * @return ChangePageOption the necessary information to request the previous / next page of results
     */
    public function getChangePageLinks()
    {
        return $this->changePageLinks;
    }

    /**
     * Adds an another result page.
     *
     * @param ChangePageOption $changePageLink The result page to add.
     */
    public function addChangePageLink(ChangePageOption $changePageLink)
    {
        $this->changePageLinks[] = $changePageLink;
    }
   
}

