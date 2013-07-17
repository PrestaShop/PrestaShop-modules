<?php

require_once 'FilterOptionBase.php';

/**
 * Container class representing a set of filtering options regrouped by attribute.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class FilterOptionGroupBase
{
    
    private $filteredAttributeName = "";

    private $multiSelect = false;

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
        $option->setGroupMultiSelect( $this->multiSelect );

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
        $this->updateOptions();
    }

    /**
     * Sets multi selection support for attribute
     * @param boolean $multiSelect true = yes
     */
    public function setMultiSelect($multiSelect)
    {
        $this->multiSelect = $multiSelect;
        $this->updateOptions();
    }


    /**
     * Sync all options properties within the group
     */
    protected function updateOptions()
    {
        foreach( $this->filteringOptions as $option )
        {
            $option->setFilteredAttributeName( $this->filteredAttributeName );
            $option->setGroupMultiSelect( $this->multiSelect );
        }

    }


    /**
     * Gets multi selection support for attribute
     * @return bool True if the attribute supports multiple selection
     */
    public function isMultiSelect()
    {
        return $this->multiSelect;
    }


}

