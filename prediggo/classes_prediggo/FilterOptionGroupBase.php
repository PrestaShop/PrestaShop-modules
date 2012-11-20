<?php

require_once 'FilterOptionBase.php';

/**
 * Container class respresenting a set of filtering options regrouped by attribute.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class FilterOptionGroupBase
{
    
    private $filteredAttributeName = "";

    private $filteringOptions = array();

    /**
     * Retrieves a list of options describing possible filter values for filteredAttributeName.
     * @return array A list of FilterOptionBase objects 
     */
    public function getFilteringOptions()
    {
        return $this->filteringOptions;
    }

    /**
     * Adds an entry to the set of possible filtering values,
     * the filteredAttributeName of the added entry is updated so that it corresponds to the group
     * @param FilterOptionBase $option The entry to add.
     */
    public function addFilteringOption(FilterOptionBase $option)
    {
        $option->setFilteredAttributeName( $this->filteredAttributeName );
        $this->filteringOptions[] = $option ;
    }

    /**
     * Get the name of the attribute
     * @return string the name of the attribute (ex : "genre", "brand")
     */
    public function getFilteredAttributeName()
    {
        return $this->filteredAttributeName;
    }

    /**
     * Set the name of the attribute, options in the list will be updated
     * @param string $filteredAttributeName the name of the attribute (ex : "genre", "brand")
     */
    public function setFilteredAttributeName( $filteredAttributeName)
    {
        $this->filteredAttributeName = $filteredAttributeName;

        foreach($this->filteringOptions as $opt   )
            $opt->setFilteredAttributeName( $filteredAttributeName);

    }

}

