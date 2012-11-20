<?php

/**
 * Basic description of an option containing the information needed to fetch an another page of results. Typical candidates are previous/next pages
 * in search queries.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ChangePageOption
{


    private $searchRefiningOption = "";
    private $label = "";


    /**
     * Returns a short text telling what the option is about. Typical values can be "next", "previous".
     * @return string The label describing the option.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the description of the option
     * @param string $label The label describing the option.
     */
    public function setLabel( $label)
    {
        $this->label = $label;
    }


    /**
     * Get the search option that can be used in a parameter object to reconduct the searching using this corrected form as input.
     * @return string the search option to use in a GetXXXRecommendationParam object.
     */
    public function getSearchRefiningOption()
    {
        return $this->searchRefiningOption;
    }

    /**
     * Set the search option that can be use in a parameter object to reconduct the searching using this corrected form as input.
     * @param string $searchRefiningOption the search option to use in a GetXXXRecommendationParam object.
     */
    public function setSearchRefiningOption($searchRefiningOption)
    {
        $this->searchRefiningOption = $searchRefiningOption;
    }

}

