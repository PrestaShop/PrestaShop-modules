<?php

/**
 * An attribute suggestion from an autocomplete request.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class AttributeSuggestion
{

    private $attributeName = "";
    private $attributeValue = "";
    private $searchQuery = "";
    private $nbOccurrences = 0;

    /**
     * Get the number of possible matches with this suggestion
     * @return integer The number of occurrences
     */
    public function getNbOccurrences()
    {
        return $this->nbOccurrences;
    }

    /**
     * Set the number of possible matches with this suggestion
     * @param integer $nbOccurrences The number of occurrences
     */
    public function setNbOccurrences($nbOccurrences)
    {
        $this->nbOccurrences = $nbOccurrences;
    }

    /**
     * Sets the attribute name
     * @param string $attributeName The attribute name
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * gets the name of the attribute containing the matched value
     * @return string The attribute name
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * Sets the matched attribute value
     * @param string $attributeValue The attribute value
     */
    public function setAttributeValue($attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }

    /**
     * Gets the matched attribute value
     * @return string The attribute value
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * Sets the search query that can be given to the search engine
     * @param string $searchQuery The search query
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * Gets the search query that can be given to the search engine
     * @return string the search query
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }




}

