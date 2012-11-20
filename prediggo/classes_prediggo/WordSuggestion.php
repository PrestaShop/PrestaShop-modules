<?php

/**
 * A word suggestion from an autocomplete request.
 *
 * @package prediggo4php
 * @subpackage types
 *
 * @author Stef
 */
class WordSuggestion
{

    private $word = "";
    private $nbOccurrences = 0;

    /**
     * Get the number of possible matches with this word
     * @return integer The number of occurrences
     */
    public function getNbOccurrences()
    {
        return $this->nbOccurrences;
    }

    /**
     * Set the number of possible matches with this word
     * @param integer $nbOccurrences The number of occurrences
     */
    public function setNbOccurrences($nbOccurrences)
    {
        $this->nbOccurrences = $nbOccurrences;
    }

    /**
     * Get the suggested word
     * @return string The suggested word
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * Set the suggested word
     * @param string $word The suggested word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }

}

