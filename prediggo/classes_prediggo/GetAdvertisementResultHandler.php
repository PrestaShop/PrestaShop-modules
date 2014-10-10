<?php

require_once "AdBlock.php";
require_once "AdResult.php";

require_once "GetAdvertisementResult.php";
require_once "DefaultResultHandler.php";


/**
 * XML content handler for getAdvertisement documents.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class GetAdvertisementResultHandler  extends  DefaultResultHandler  {

     /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param GetAdvertisementResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node, $resultObj) {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {

            //items list
            case "page":

                $this->readPageResult($node, $resultObj);
                return true;
        }

        return false;
    }

    /**
     * Read and parse a profileResult
     * @param DOMNode $pageNode the "<page>" node
     * @param GetAdvertisementResult $resultObj the object to fill
     */
    protected function readPageResult(DOMNode $pageNode, GetAdvertisementResult  $resultObj) {

        //read profile attributes
        foreach ( $pageNode->attributes as $attribute )
        {
            switch ($attribute->name)
            {
                case "ID":
                    $resultObj->setPageId( intval( $attribute->value ) );
                    break;

                case "name":
                    $resultObj->setPageName( $attribute->value );
                    break;
            }
        }

        //read suggestion blocks
        foreach ( $pageNode->childNodes as $blockNode ) {

            switch ($blockNode->nodeName) {
                case "block":

                    //parse product element
                    $block = new AdBlock();
                    $this->readBlock( $blockNode, $block);
                    $resultObj->addBlock( $block );

                    break;
            }
        }
    }


    /**
     * Reads a word element.
     * @param DOMNode $blockNode an xml element representing a block
     * @param AdBlock $block the block object to fill.
     */
    protected function readBlock(DOMNode $blockNode, AdBlock $block) {

        //read attributes
        foreach ( $blockNode->attributes as $attribute ) {

            switch ($attribute->name) {
                case "ID":
                    $block->setBlockId( intval( $attribute->value ) );
                    break;

                case "name":
                    $block->setBlockName( $attribute->value );
                    break;
            }
        }

        //read  blocks
        foreach ( $blockNode->childNodes as $adNode ) {

            switch ($adNode->nodeName) {
                case "ad":

                    //parse ad element
                    $ad = new AdResult();
                    $this->readAd( $adNode, $ad);
                    $block->addAd( $ad );

                    break;
            }
        }
    }



    /**
     * Reads a AD element.
     * @param DOMNode $adNode an xml element representing a product
     * @param AdResult $ad the advertisement object to fill.
     */
    protected function readAd(DOMNode $adNode, AdResult $ad) {
        //read attributes
        foreach ( $adNode->attributes as $attribute ) {

            switch ($attribute->name)
            {
                case "name":
                    $ad->setAdName( $attribute->value );
                    break;

                case "pictureUrl":
                    $ad->setPictureUrl($attribute->value );
                    break;
                
                case "clickUrl" :
                    $ad->setClickUrl($attribute->value);
                    break;

                case "clickID" :
                    $ad->setClickId($attribute->value);
                    break;
                
            }
        }

        //read  blocks
        foreach ( $adNode->childNodes as $propertiesNode ) {

            switch ($propertiesNode->nodeName) {
                case "properties":

                    //parse properties element
                    $this->readProperties( $propertiesNode, $ad);
                    //$block->addAd( $ad );

                    break;
            }
        }
    }

    protected function readProperties(DOMNode $propertiesNode, AdResult $ad)
    {

        //read props
        foreach ( $propertiesNode->childNodes as $propertyNode ) {

            switch ($propertyNode->nodeName) {
                case "property":

                    $key   = '';
                    $value = '';

                    foreach ( $propertyNode->attributes as $attribute ) {

                        switch ($attribute->name) {
                            case "name":
                                $key = $attribute->value ;
                                break;

                            case "value":
                                $value = $attribute->value;
                                break;
                        }
                    }

                    if( !empty( $key) ) {
                        $ad->addProperty($key, $value);
                    }

                    break;
                }
        }

    }
    
}

