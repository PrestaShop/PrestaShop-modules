<?php

/**
 * Classe abstraite pour les retours de script avec fonction magique retournant la valeur des attributs
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class XMLResult extends XMLElement {

    public function __call($name, array $params) {
        //fonction returnItem : retourne la valeur de l'attribute Item si existant, null sinon
        if (preg_match('#^return.+$#', $name)) {
            $elementname =  strtolower(preg_replace('#^return(.+)$#', '$1', $name));
            
            return array_key_exists($elementname, $this->getAttributes()) ? $this->getAttribute($elementname) : null;
        }

        return parent::__call($name, $params);
    }

}