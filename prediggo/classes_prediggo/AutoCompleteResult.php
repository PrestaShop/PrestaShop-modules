<?php

require_once "RequestResultBase.php";


/**
 * Result class for AutoComplete queries
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class AutoCompleteResult extends RequestResultBase
{
    protected $inputString = "";

    protected $suggestionProfiles = array();



    /**
     * Gets the list of suggested words. This is a shortcut method for shops having <strong>only one profile</strong>.
     * If your shop has more than one profile, you should rather consider using {@link AutoCompleteResult#getSuggestionsProfiles()} which sorts suggestions by profile.
     * @return array an array of WordSuggestion objects (all profiles put together).
     */
    public function getSuggestedWords()
    {
        $reco = array();

        foreach( $this->suggestionProfiles as $profile)
            $reco = array_merge( $reco , $profile->getSuggestedWords() );

        return $reco;
    }



    /**
     * Gets the list of suggested products. This is a shortcut method for shops having <strong>only one profile</strong>.
     * If your shop has more than one profile, you should rather consider using {@link AutoCompleteResult#getSuggestionsProfiles()} which sorts suggestions by profile.
     * @return array an array of ProductSuggestion objects (all profiles put together).
     */
    public function getSuggestedProducts()
    {
        $reco = array();

        foreach( $this->suggestionProfiles as $profile)
            $reco = array_merge( $reco , $profile->getSuggestedProducts() );

        return $reco;
    }

    /**
     * Gets the list of suggested attributes. This is a shortcut method for shops having <strong>only one profile</strong>.
     * If your shop has more than one profile, you should rather consider using {@link AutoCompleteResult#getSuggestionsProfiles()} which sorts suggestions by profile.
     * @return array an array of AttributeSuggestion objects (all profiles put together).
     */
    public function getSuggestedAttributes()
    {
        $reco = array();

        foreach( $this->suggestionProfiles as $profile)
            $reco = array_merge( $reco , $profile->getSuggestedAttributes() );

        return $reco;
    }

    /**
     * Gets a list of suggestions grouped in profiles.
     * @return ProfileSuggestions A list of suggestions grouped in profiles
     */
    public function getSuggestionsProfiles()
    {
        return $this->suggestionProfiles;
    }

    /**
     * Adds a new profile and its list of suggestions to the results.
     * 
     * @param ProfileSuggestions $profileSug The suggestion profile to add
     */
    public function addSuggestionsProfile( ProfileSuggestions $profileSug )
    {
        $this->suggestionProfiles[] = $profileSug;
    }



   /**
     * Get the researched input string.
     *
     * @return string the researched input string
     */
    public function getInputQuery()
    {
        return $this->inputString;
    }

    /**
     * Set the researched input string
     *
     * @param string $inputString the researched input string
     */
    public function setInputQuery( $inputString)
    {
        $this->inputString = $inputString;
    }
}

