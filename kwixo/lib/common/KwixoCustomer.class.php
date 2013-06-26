<?php

/**
 * Class for the <utilisateur> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoCustomer extends KwixoXMLElement {

  const TYPE_ENTREPRISE = 1;
  const TYPE_PARTICULIER = 2;

  public function __construct() {
    parent::__construct('utilisateur');
  }

  /**
   * creates an object KwixoXMLElement reprensenting <siteconso>, adds it to the current object and sets its children, then returns it
   * 
   * @param float $ca amount paid by the customer since his first order
   * @param int $nb number of order the customer passed since the first one
   * @param string $datepremcmd date of the very first order passed by the customer, current order not included. Format has to be Y-m-d H:i:s
   * @param string $datederncmd date of the last order passed by the customer, current order not included. Format has to be Y-m-d H:i:s
   * @return KwixoXMLElement
   */
  public function createSiteconso($ca = null, $nb = null, $datepremcmd = null, $datederncmd = null) {
    $siteconso = $this->createChild('siteconso');
    if (!is_null($ca))
      $siteconso->createChild('ca', $ca);
    if (!is_null($nb))
      $siteconso->createChild('nb', $nb);
    if (!is_null($datepremcmd))
      $siteconso->createChild('datepremcmd', $datepremcmd);
    if (!is_null($datederncmd))
      $siteconso->createChild('datederncmd', $datederncmd);
    
    return $siteconso;
  }

}