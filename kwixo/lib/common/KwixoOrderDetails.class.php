<?php

/**
 * Class for the <infocommande> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoOrderDetails extends KwixoXMLElement {

  public function __construct() {
    parent::__construct('infocommande');
  }

  /**
   * creates a KwixoCarrier object representing element <transport>, adds it to the current element, adds sub-children, then returns it
   * 
   * @param string $name carrier name
   * @param string $type carrier type (1|2|3|4|5)
   * @param type $speed carrier speed (1 means express, 2 means standard)
   * @return KwixoCarrier
   */
  public function createCarrier($name, $type, $speed) {
    $carrier = $this->addChild(new KwixoCarrier());
    $carrier->createChild('nom', $name);
    $carrier->createChild('type', $type);
    $carrier->createChild('rapidite', $speed);

    return $carrier;
  }

  /**
   * creates a KwixoProductList object representing element <list>, adds it to the current element, then returns it
   * 
   * @return KwixoProductList
   */
  public function createProductList() {
    $product_list = $this->addChild(new KwixoProductList());
    return $product_list;
  }

}