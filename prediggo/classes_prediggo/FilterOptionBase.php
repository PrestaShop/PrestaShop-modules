<?php

/**
 * Base class for filtering options.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class FilterOptionBase
{
   
    private $rangeValueMin;
    private $rangeValueMax;

    private $discreteValue ;

    private $textValue ;
    private $searchRefiningOption;

    private $filteredAttributeName;
    private $groupMultiSelect ;



    /**
     * Get the name of the attribute
     * @return string the name of the attribute (ex : "genre", "brand")
     */
    public function getFilteredAttributeName()
    {
        return $this->filteredAttributeName;
    }

    /**
     * Sets the name of the attribute
     * @param string $attName the name of the attribute (ex : "genre", "brand")
     */
    public function setFilteredAttributeName($attName)
    {
        $this->filteredAttributeName = $attName;
    }


    /**
     * Tests if this filter is of range type.
     * @return boolean true = range, false = discrete
     */
    public function isRange()
    {
        return  empty( $this->discreteValue);
    }

     /**
     * Tests if this filter is of discrete type.
     * @return boolean True = discrete, false = range
     */
    public function isDiscrete()
    {
        return !$this->isRange();
    }

    /**
     * Get the filtered attribute value.
     * @return string The filtered attribute value value, or null if this filter is of <b>range</b> type.
     */
    public function getDiscreteValue()
    {
        return $this->discreteValue;
    }

    /**
     * Set the filter value.
     * @param string $discreteValue The filter value, or null if this filter is of <b>range</b> type.
     */
    public function setDiscreteValue( $discreteValue)
    {
        $this->discreteValue = $discreteValue;
    }


    /**
     * Get the search option that can be used in a parameter object to reconduct the searching using the filtering criteria described by this object.
     * @return string the search option to use in a GetXXXRecommendationParam object.
     */
    public function getSearchRefiningOption()
    {
        return $this->searchRefiningOption;
    }

    /**
     * Set the search option that can be used in a parameter object to reconduct the searching using the filtering criteria described by this object.
     * @param string $searchRefiningOption the search option to use in a GetXXXRecommendationParam object.
     */
    public function setSearchRefiningOption( $searchRefiningOption)
    {
        $this->searchRefiningOption = $searchRefiningOption;
    }


    /**
     * Get the upper bound of a filter of range type.
     * @return string The filtered attribute max value (exclusive), or null if this filter is of type <b>discrete</b>.
     */
    public function getRangeValueMax()
    {
        return $this->rangeValueMax;
    }

    /**
     * Set the upper bound of a filter of range type.
     * @param string $rangeValueMax The filtered attribute max value (exclusive), use null if this filter is of type <b>discrete</b>.
     */
    public function setRangeValueMax( $rangeValueMax)
    {
        $this->rangeValueMax = $rangeValueMax;
    }

    /**
     * Get the lower bound of a filter of range type.
     * @return string The filtered attribute min value (inclusive), or null if this filter is of type <b>discrete</b>.
     */
    public function getRangeValueMin()
    {
        return $this->rangeValueMin;
    }

    /**
     * Set the lower bound of a filter of range type.
     * @param string $rangeValueMin The filtered attribute min value (inclusive), use null if this filter is of type <b>discrete</b>.
     */
    public function setRangeValueMin( $rangeValueMin)
    {
        $this->rangeValueMin = $rangeValueMin;
    }

    /**
     * Gets the text value, can be used to display a clickable label on a web page in most simple scenarios.
     * @return string The text value
     */
    public function getTextValue()
    {
        return $this->textValue;
    }

    /**
     * Sets the text value, can be used to display a clickable label on a web page in most simple scenarios.
     * @param string $textValue The text value
     */
    public function setTextValue( $textValue)
    {
        $this->textValue = $textValue;
    }


    /**
     * Gets multi selection support for attribute
     * @return boolean True if the attribute supports multiple selection within its group
     */
    public function isGroupMultiSelect()
    {
        if( $this->getParentGroup() == null )
            return false;

        return $this->getParentGroup()->isMultiSelect();
    }

    /**
     * Sets multi selection support for attribute
     * @param boolean $multiSelect True if the attribute supports multiple selection within its group
     */
    public function setGroupMultiSelect($multiSelect)
    {
        $this->groupMultiSelect = $multiSelect;
    }

}

