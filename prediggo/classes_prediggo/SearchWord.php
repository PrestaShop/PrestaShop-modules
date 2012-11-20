<?php
/**
 * Represents a word in its eventually-corrected form.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class SearchWord
{
    private $word = "";
    private $wrong = FALSE;
    private $searchRefiningOption = "";

    /**
     * Gets the word
     *
     * @return string the value of word
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * Sets the value of word
     *
     * @param string $word new value of word
     */
    public function setWord( $word)
    {
        $this->word = $word;
    }


    /**
     * Tells if this word is different from the one that was given in the searchString.
     *
     * @return boolean the value of wrong
     */
    public function isWrong()
    {
        return $this->wrong;
    }

    /**
     * Sets the value of wrong
     *
     * @param boolean $wrong new value of wrong
     */
    public function setWrong( $wrong)
    {
        $this->wrong = $wrong;
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