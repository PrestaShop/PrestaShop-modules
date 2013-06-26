<?php

class KwixoXMLParams extends DOMDocument {
  protected $root;
  
  public function __construct() {
    parent::__construct('1.0', 'UTF-8');
    $this->root = $this->appendChild(new KwixoXMLElement('ParamCBack'));
  }
  
  /**
   * adds a param that will be returned with other Kwixo params during Kwixo calls (on URLSys and URLCall)
   * 
   * @param string $name param name
   * @param string $value param value
   * @return KwixoXMLElement
   */
  public function addParam($name, $value){
    $obj = $this->root->appendChild(new KwixoXMLElement('obj'));
    $obj->appendChild(new KwixoXMLElement('name', $name));
    $obj->appendChild(new KwixoXMLElement('value', $value));
    return $obj;
  }
}