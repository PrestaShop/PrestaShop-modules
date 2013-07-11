<?php

/**
 * Class for the <pointrelais> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoDropOffPoint extends KwixoXMLElement {

  public function __construct() {
    parent::__construct('pointrelais');
  }

  /**
   * creates an object KwixoXMLElement representing the element <adresse> and adds it as a child of the current element, then returns it
   * 
   * @param string $main_street
   * @param string $zipcode
   * @param string $city
   * @param string $country
   * @param string $secondary_street
   * @return KwixoXMLElement
   */
  public function createAddress($main_street, $zipcode, $city, $country, $secondary_street) {
    $address = $this->createChild('adresse');
    $address->createChild('rue1', $main_street);
    if (!is_null($secondary_street))
      $address->createChild('rue2', $secondary_street);
    $address->createChild('cpostal', $zipcode);
    $address->createChild('ville', $city);
    $address->createChild('pays', $country);

    return $address;
  }

}