<?php

/**
 * Class for the tag <adresse>
 *
 * @author ESPIAU Nicolas
 */
class CertissimAdresse extends CertissimXMLElement
{

  const FORMAT = 1;

  public function __construct($type = null, $rue1 = null, $rue2 = null, $cpostal = null, $ville = null, $pays = null, XMLElement $appartement = null)
  {
    parent::__construct();

    if (!is_null($type))
      $this->addAttribute('type', $type);
    $this->addAttribute('format', self::FORMAT);

    $this->childRue1($rue1);
    $this->childRue2($rue2);
    $this->childCpostal($cpostal);
    $this->childVille($ville);
    $this->childPays($pays);
    if (!is_null($appartement))
      $this->childAppartement($appartement);
  }

}