<?php

require_once "ChangePageOption.php";
require_once "SearchWord.php";
require_once "GetSearchRecommendationResultBase.php";
require_once "GetRecommendationResultHandler.php";



/**
 * XML content handler specific to search-based requests. Includes didYouMean string.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class GetSearchRecommendationBaseResultHandler extends GetRecommendationResultHandler
{

    /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param GetSearchRecommendationResultBase $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node,  $resultObj)
    {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "didyoumean":

                //didyoumean string
                $this->readDidYouMean($node, $resultObj);
                return true;

            case "changepageoptions":

                //change page links
                $this->readChangePageOptions($node, $resultObj);
                return true;


            case "searchstatistics":
                $this->readSearchStatistics($node, $resultObj);
                return true;

        }

        return false;
    }


    private function readSearchStatistics(DOMNode $statNode,  GetSearchRecommendationResultBase $resultObj)
    {
        $stats = new SearchStatistics();


        //read stats attributes
        foreach ( $statNode->attributes as $attribute )
        {
            switch ($attribute->name)
            {
                case "timeInMS":
                    $stats->setTimeInMs( floatval( $attribute->value) );
                    break;

                case "activePageID":
                    $stats->setCurrentPageNumber( intval($attribute->value) );
                    break;

                case "totalNbResults":
                    $stats->setTotalSearchResults( intval($attribute->value) );
                    break;

                case "nbResultsInCurrentPage":
                    $stats->setResultsInCurrentPage( intval($attribute->value) );
                    break;
                
                case "maxNbResultsPerPage":
                    $stats->setMaxResultsPerPage( intval($attribute->value) );
                    break;


                case "totalNbSearchPages":
                    $stats->setTotalSearchPages( intval( $attribute->value) );
                    break;

                case "userQuery":
                    $stats->setUserQuery( $attribute->value );
                    break;

                case "sortingCodeUsed":
           
                    $stats->setCurrentSorting( intval( $attribute->value) );
                    break;

                case "languageCode":
                    $stats->setLanguageCode( $attribute->value );
                    break;

            }
        }

        $resultObj->setSearchStatistics( $stats);
    }

 

    /**
     * Reads a changePageOptions element and adds the content to the result object.
     * @param DOMNode $changePageNode the node being inspected
     * @param GetSearchRecommendationResultBase $resultObj the result object to fill
     */
    private function readChangePageOptions( DOMNode $changePageNode, GetSearchRecommendationResultBase $resultObj )
    {
        foreach( $changePageNode->childNodes as $optionNode)
        {
            if( $optionNode->nodeName == "changepageoption")
            {
                $changePageLink = new ChangePageOption();
                $changePageLink->setLabel( $optionNode->textContent );

                //read word attributes
                foreach ( $optionNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "searchRefiningOptions":
                            $changePageLink->setSearchRefiningOption( $attribute->value );
                            break;
                    }
                }

                $resultObj->addChangePageLink($changePageLink);
            }
        }


    }

    /**
     * Reads a didYouMean element and adds the content to the result object.
     * @param DOMNode $didYouMeanNode the node being inspected
     * @param GetSearchRecommendationResultBase $resultObj the result object to fill
     */
    private function readDidYouMean( DOMNode $didYouMeanNode, GetSearchRecommendationResultBase $resultObj )
    {
        //didyoumean string
        foreach( $didYouMeanNode->childNodes as $wordNode)
        {
            if( $wordNode->nodeName == "word")
            {
                $word = new SearchWord();
                $word->setWord( $wordNode->textContent );

                //read word attributes
                foreach ( $wordNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "bold":
                            if( $attribute->value == "true" )
                                $word->setWrong( TRUE );
                            break;

                        case "searchRefiningOptions":
                            $word->setSearchRefiningOption( $attribute->value );
                            break;
                    }
                }

                $resultObj->addDidYouMeanWord( $word);
            }
        }
    }
    
}

