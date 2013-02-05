<?php

/**
 * Objet XML reçu en réponse au script get_validstack
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class StackResponse extends XMLResult {
    public function getResults() {
        $results = array();

        foreach ($this->getChildrenByName('result') as $result) {
            $results[] = new ResultResponse($result->getXML());
        }

        return $results;
    }
}