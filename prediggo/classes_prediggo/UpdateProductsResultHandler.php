<?php



require_once "UpdateProductsResult.php";
require_once "DefaultResultHandler.php";


/**
 * XML content handler for UpdateProducts documents.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class UpdateProductsResultHandler  extends  DefaultResultHandler
{

     /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param UpdateProductsResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node, $resultObj)
    {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "productsupdated":
                $this->readProductsUpdated( $node, $resultObj );
                return true;

        }

        return false;
    }


    /**
     * Reads a productsupdated element.
     * @param DOMNode $productsUpdatedNode an xml element
     * @param UpdateProductsResult $result The object which will be returned to the end-user
     */
    protected function readProductsUpdated(DOMNode $productsUpdatedNode, UpdateProductsResult $result)
    {
        //read suggestion blocks
        foreach ( $productsUpdatedNode->childNodes as $idNode )
        {
            if( $idNode->nodeName == "id")
            {

                $status = false;

                //read attributes
                foreach ( $idNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "status":

                            if($attribute->value == "true")
                                $status = true;
                            break;
                    }
                }

                if( $status)
                    $result->addUpdatedItemId( $idNode->textContent );
                else
                    $result->addFailedItemId( $idNode->textContent );

            }

        }

    }


}

