<?php

/**
 * Class for the <infocommande> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauOrderDetails extends SceauXMLElement {

  public function __construct() {
    parent::__construct('infocommande');
  }

  /**
   * creates a SceauCarrier object representing element <transport>, adds it to the current element, adds sub-children, then returns it
   * 
   * @param string $name carrier name
   * @param string $type carrier type (1|2|3|4|5)
   * @param type $speed carrier speed (1 means express, 2 means standard)
   * @return SceauCarrier
   */
  public function createCarrier($name, $type, $speed) {
    $carrier = $this->addChild(new SceauCarrier());
    $carrier->createChild('nom', $name);
    $carrier->createChild('type', $type);
    $carrier->createChild('rapidite', $speed);

    return $carrier;
  }

  /**
   * creates a SceauProductList object representing element <list>, adds it to the current element, then returns it
   * 
   * @return SceauProductList
   */
  public function createProductList() {
    $product_list = $this->addChild(new SceauProductList());
    return $product_list;
  }

}