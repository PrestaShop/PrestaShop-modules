<?php

require_once( 'GetRecommendationResultHandler.php' );


/**
 * Result handler for GetItemRecommendation queries.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class GetItemRecommendationResultHandler extends GetRecommendationResultHandler
{

    
    /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param GetItemRecommendationResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node,  $resultObj)
    {
        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "item":
                $resultObj->setItemId( $node->textContent );
                return true;

            case "bundles":

                //bundles list
                foreach( $node->childNodes as $itemNode)
                {
                    if ( $itemNode->nodeName == "item")
                    {
                        $item = new BundleRecommendation();
                        $this->readItem( $itemNode, $item);

                        $resultObj->addRecommendedBundles( $item);
                    }
                }
                return true;

        }

        return false;
    }
}
