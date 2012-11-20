<?php

require_once("RequestResultBase.php");

/**
 * Handle the transformation step common to all webservice queries.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class DefaultResultHandler
{

        /**
         * Handle the transformation of an xml document to a result object.
         * @param DOMDocument $xmlDocument The xml document to read.
         * @param RequestResultBase $resultObj The result object to fill.
         */
        public function transform(DOMDocument $xmlDocument, RequestResultBase $resultObj)
        {
            //read the xml node by node
            foreach($xmlDocument->documentElement->childNodes as $directChild)
                $this->handleXmlReaderCurrentNode($directChild, $resultObj);
        }

       
        /**
         * Handles the current node in the xml reading loop.
         * Warning : Don't forget to call parent method...
         * @param DOMNode $node The current xml node
         * @param RequestResultBase $resultObj The object which will be returned to the end-user
         * @return boolean True if the node was handled
         */
        protected function handleXmlReaderCurrentNode(DOMNode $node,  $resultObj)
        {

            switch ($node->nodeName)
            {
                case "request":
                    $resultObj->setSessionId( $node->textContent );
                    return true;

                case "status":
                    $resultObj->setStatus ((int) $node->textContent );
                    return true;

                case "timems":
                    $resultObj->setTimeInMs ( floatval( $node->textContent )  );
                    return true;
            }

            return false;
        }

}

