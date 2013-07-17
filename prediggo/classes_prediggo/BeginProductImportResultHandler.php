<?php


require_once "BeginProductImportResult.php";
require_once "DefaultResultHandler.php";


/**
 * XML content handler for BeginProductImport documents.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class BeginProductImportResultHandler  extends  DefaultResultHandler
{

     /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param BeginProductImportResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node, $resultObj)
    {

        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "tokenid":
                $resultObj->setTransactionId( $node->textContent );
                return true;
        }

        return false;
    }


    
}

