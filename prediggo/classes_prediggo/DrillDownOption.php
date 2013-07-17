<?php

require_once "FilterOptionBase.php";

/**
 * An option describing a possible filter on a particular search query resultset. There are two kinds of options, they can be either of <b>discrete</b> type in which case they
 * require the attribute to match an exact value, or of <b>range</b> type in case they need it to be somewhere between 2 values.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class DrillDownOption extends FilterOptionBase
{
    private $nbOccurences = 0;
    private $selected = false;

    /**
     * Get the number of results (items) matching the described filter.
     * @return integer The number of results (items) matching the described filter
     */
    public function getNbOccurences()
    {
        return $this->nbOccurences;
    }

    /**
     * Set the number of results (items) matching the described filter.
     * @param integer $nbOccurences The number of results (items) matching the described filter
     */
    public function setNbOccurences( $nbOccurences)
    {
        $this->nbOccurences = $nbOccurences;
    }

    /**
     * Gets this option state (active or not)
     * @return boolean True if the attribute supports multiple selection within its group
     */
    public function isSelected()
    {
        return $this->selected;
    }


    /**
     * Sets the filter state (active or not)
     * @param boolean $selected True = active
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }
}

