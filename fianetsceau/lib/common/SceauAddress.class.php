<?php

/**
 * Class for <adresse> elements
 * 
 * @author ESPIAU Nicolas
 */
class SceauAddress extends SceauXMLElement {

  const FORMAT = 1;

  public function __construct() {
    parent::__construct('adresse');
  }

  /**
   * creates an object SceauXMLElement representing the element <appartement>, child of <adresse>, adds it to the current object then returns it
   * 
   * @param string $digicode1 first entry code
   * @param string $digicode2 seconde entry code
   * @param string $stairway name or number of the stairway to the flat
   * @param string $floor name or number of the floor
   * @param string $door name or number of the door of the flat
   * @param string $building name or number of the building
   * @return SceauXMLElement
   */
  public function createFlat($digicode1 = null, $digicode2 = null, $stairway = null, $floor = null, $door = null, $building = null) {
    $flat = $this->createChild('appartement');
    if (!is_null($digicode1))
      $flat->createChild('digicode1', $digicode1);
    if (!is_null($digicode2))
      $flat->createChild('digicode2', $digicode2);
    if (!is_null($stairway))
      $flat->createChild('escalier', $stairway);
    if (!is_null($floor))
      $flat->createChild('etage', $floor);
    if (!is_null($door))
      $flat->createChild('nporte', $door);
    if (!is_null($building))
      $flat->createChild('batiment', $building);

    return $flat;
  }

}