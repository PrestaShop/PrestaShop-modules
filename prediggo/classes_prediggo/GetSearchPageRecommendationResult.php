<?php

require_once "GetSearchRecommendationResultBase.php";


/**
 * This class represents the result of a GetSearchPageRecommendation query.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class GetSearchPageRecommendationResult extends GetSearchRecommendationResultBase
{

    private $customRedirections = array();
    private $drillDownGroups = array();
    private $sortingOptions = array();
    private $cancellableFiltersGroups = array();

    /**
     * Adds a group of cancellable options to the list of active filters...
     *
     * @param CancellableOptionGroup $group The group to add.
     */
    public function addCancellableFiltersGroup(CancellableOptionGroup $group)
    {
        $this->cancellableFiltersGroups[] = $group;
    }

    /**
     * Get a list of currently active filters grouped by attribute that can be disabled.
     *
     * @return array An array of CancellableOptionGroup objects
     */
    public function getCancellableFiltersGroups()
    {
        return $this->cancellableFiltersGroups;
    }


    /**
     * Adds a group of option to the possible filters.
     *
     * @param DrillDownOptionGroup $group The group of options to add.
     */
    public function addDrillDownOptionGroup(DrillDownOptionGroup $group)
    {
        $this->drillDownGroups[] = $group;
    }

    /**
     * Get the list of option groups available to refine the query.
     *
     * @return array An array of DrillDownOptionGroup objects available to refine the query.
     */
    public function getDrillDownGroups()
    {
        return $this->drillDownGroups;
    }

    /**
     * Adds a possible sorting option to the list.
     *
     * @param SortingOption $option the sorting option
     */
    public function addSortingOption( SortingOption $option)
    {
        $this->sortingOptions[] =  $option;
    }

    /**
     * Get a list of criterias that can be used to sort the results of this query.
     *
     * @return array An array of SortingOption objects.
     */
    public function getSortingOptions()
    {
        return $this->sortingOptions ;
    }



    /**
     * Adds a new custom redirection
     * @param RedirectionObject $redirect The custom redirection object
     */
    public function addCustomRedirection( RedirectionObject $redirect)
    {
        $this->customRedirections[] = $redirect;
    }

    /**
     * Get the list of custom redirections. Custom redirections are defined by marketers in the prediggo control panel and may appear or not depending on
     * the occurence of particular keywords in the search query.
     *
     * @return array The list of custom redirections (RedirectionObject objects).
     */
    public function getCustomRedirections( )
    {
        return $this->customRedirections ;
    }
}

