<?php

require_once "WordSuggestion.php";
require_once "AttributeSuggestion.php";
require_once "ProductSuggestion.php";
require_once "ProfileSuggestions.php";

require_once "AutoCompleteResult.php";
require_once "DefaultResultHandler.php";


/**
 * XML content handler for AutoComplete documents.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class AutoCompleteResultHandler  extends  DefaultResultHandler
{

     /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param AutoCompleteResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node, $resultObj)
    {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "inputQuery":
                $resultObj->setInputQuery( $node->textContent );
                return true;


            //items list
            case "suggestions":

                $this->readProfileResult($node, $resultObj);
                return true;
        }

        return false;
    }

    /**
     * Read and parse a profileResult
     * @param DOMNode $suggestionsNode the "suggestions" node
     * @param AutoCompleteResult $resultObj the object to fill
     */
    protected function readProfileResult(DOMNode $suggestionsNode, AutoCompleteResult  $resultObj)
    {
        //profile
        $suggProfile = new ProfileSuggestions();

        //read profile attributes
        foreach ( $suggestionsNode->attributes as $attribute )
        {
            switch ($attribute->name)
            {
                case "classID":
                    $suggProfile->setProfileMapId( intval( $attribute->value ) );
                    break;

                case "profileName":
                    $suggProfile->setProfileName( $attribute->value );
                    break;
            }
        }

        //read suggestion blocks
        foreach ( $suggestionsNode->childNodes as $suggNode )
        {
            switch ($suggNode->nodeName)
            {
                case "word":

                    //parse word element
                    $word = new WordSuggestion();         
                    $this->readWord( $suggNode, $word);
                    $suggProfile->addSuggestedWord($word);

                    break;

                case "product":

                    //parse product element
                    $product = new ProductSuggestion();
                    $this->readProduct( $suggNode, $product);
                    $suggProfile->addSuggestedProduct($product);

                    break;

                case "attribute":

                    //parse product element
                    $attribute = new AttributeSuggestion();
                    $this->readAttribute( $suggNode, $attribute);
                    $suggProfile->addSuggestedAttribute($attribute);

                    break;
            }
        }

        $resultObj->addSuggestionsProfile($suggProfile);
    }


    /**
     * Reads a word element.
     * @param DOMNode $wordNode an xml element representing a word
     * @param WordSuggestion $word the word object to fill.
     */
    protected function readWord(DOMNode $wordNode, WordSuggestion $word)
    {
        //read content
        $word->setWord( $wordNode->textContent );

        //read attributes
        foreach ( $wordNode->attributes as $attribute )
        {

            switch ($attribute->name)
            {
                case "nbOccurences":
                    $word-> setNbOccurrences( intval( $attribute->value) );
                    break;
            }
        }
    }



    /**
     * Reads a product element.
     * @param DOMNode $prodNode an xml element representing a product
     * @param ProductSuggestion $prod the product object to fill.
     */
    protected function readProduct(DOMNode $prodNode, ProductSuggestion $prod)
    {
        //read content
        $prod->setProductName( $prodNode->textContent );

        //read attributes
        foreach ( $prodNode->attributes as $attribute )
        {

            switch ($attribute->name)
            {
                case "productID":
                    $prod->setProductId($attribute->value );
                    break;

                case "clickparameters":
                    $prod->setNotificationId($attribute->value );
                    break;
                
                default:
                    $prod->setAdditionalAttribute($attribute->name, $attribute->value);
                    break;
                
            }
        }
    }

    protected function readAttribute(DOMNode $attNode, AttributeSuggestion $att)
    {
        //read content
        $att->setAttributeValue( $attNode->textContent );

        //read attributes
        foreach ( $attNode->attributes as $attribute )
        {

            switch ($attribute->name)
            {
                case "attributeName":
                    $att->setAttributeName($attribute->value );
                    break;

                case "nbOccurences":
                    $att->setNbOccurrences( intval( $attribute->value) );
                    break;

                case "onClickQuery":
                    $att->setSearchQuery($attribute->value );
                    break;
            }
        }
    }
    
}

