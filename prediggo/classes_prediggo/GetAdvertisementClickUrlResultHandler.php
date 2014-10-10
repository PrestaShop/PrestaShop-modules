<?php

require_once "AdBlock.php";
require_once "AdResult.php";

require_once "GetAdvertisementClickUrlResult.php";
require_once "DefaultResultHandler.php";


/**
 * XML content handler for getAdvertisementClickUrl documents.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class GetAdvertisementClickUrlResultHandler  extends  DefaultResultHandler  {

     /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param GetAdvertisementClickUrlResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node, $resultObj) {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {

            //clickURL
            case "clickurl":

                $resultObj->setClickUrl( $node->textContent  );
                return true;
        }

        return false;
    }


    
}

