<?php

/**
 * Class for the <transport> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoCarrier extends KwixoXMLElement {

  const SPEED_STANDARD = 2;
  const SPEED_EXPRESS = 1;
  const TYPE_WITHDRAWAL_AT_MERCHANT = 1;
  const TYPE_DROP_OFF_POINT = 2;
  const TYPE_WITHDRAWAL_AT_AGENCY = 3;
  const TYPE_CARRIER = 4;
  const TYPE_DOWNLOAD = 5;

  public function __construct() {
    parent::__construct('transport');
  }

  /**
   * creates an object KwixoDropOffPoint representing the element <pointrelais>, adds it to the current object, then returns it
   * 
   * @param string $name
   * @param string $id
   * @param KwixoXMLElement $address
   * @return KwixoDropOffPoint
   */
  public function createDropOffPoint($name = null, $id = null, KwixoXMLElement $address = null) {
    $drop_off_point = $this->addChild(new KwixoDropOffPoint());
    if (!is_null($name))
      $drop_off_point->createChild('enseigne', $name);
    if (!is_null($id))
      $drop_off_point->createChild('identifiant', $id);
    if (!is_null($address))
      $drop_off_point->addChild($address);

    return $drop_off_point;
  }

}