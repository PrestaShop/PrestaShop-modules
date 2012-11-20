<?php

require_once( 'DefaultResultHandler.php' );

/**
 * Result handler for RegisterUser queries.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class RegisterUserResultHandler extends  DefaultResultHandler
{

    /**
    * Handles the current node in the xml reading loop.
    * @param DOMNode $node The current xml node
    * @param RegisterUserResult $resultObj The object which will be returned to the end-user
    * @return boolean True if the node was handled
    */
    protected function handleXmlReaderCurrentNode(DOMNode $node,  $resultObj)
    {
        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "user":
                $resultObj->setUserId( $node->textContent );
                return true;
        }

        return false;
    }

}

