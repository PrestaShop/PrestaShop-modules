<?php


require_once 'WordSuggestion.php';
require_once 'ProductSuggestion.php';

/**
 * Products and words suggestions in a given profile, resulting from an autocomplete request.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class ProfileSuggestions
{

    protected  $suggestedWords = array();
    protected  $suggestedProducts = array();

    protected  $profileName = "";
    protected  $profileMapId = 0;

    /**
     * Gets the list of suggested words.
     * @return array an array of WordSuggestion objects
     */
    public function getSuggestedWords()
    {
        return $this->suggestedWords;
    }


    /**
     * Adds a new suggested word in this profile. This function should not be called
     * from customer code.
     *
     * @param WordSuggestion $word The word to add.
     */
    public function addSuggestedWord( WordSuggestion $word )
    {
        $this->suggestedWords[] = $word;
    }



    /**
     * Gets the list of recommended products.
     * @return array an array of ProductSuggestion objects
     */
    public function getSuggestedProducts()
    {
        return $this->suggestedProducts;
    }

    /**
     * Adds a new suggested product in this profile. This function should not be called
     * from customer code.
     *
     * @param ProductSuggestion $product The product to add.
     */
    public function addSuggestedProduct( ProductSuggestion $product )
    {
        $this->suggestedProducts[] = $product;
    }



    /**
     * Gets the profile name
     * @return string the profile name
     */
    public function getProfileName()
    {
        return $this->profileName;
    }

    /**
     * Sets the profile name
     * @param string $profileName the profile name to set
     */
    public function setProfileName( $profileName) {
        $this->profileName = $profileName;
    }

    /**
     * Gets the mapped profile identifier
     * @return integer the profile identifier
     */
    public function getProfileMapId()
    {
        return $this->profileMapId;
    }

    /**
     * Sets the mapped profile identifier
     * @param integer $profileMapId the profile identifier
     */
    public function setProfileMapId( $profileMapId )
    {
        $this->profileMapId = $profileMapId;
    }

}



