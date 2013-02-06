<?php

/**
 * class for tag <siteconso>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimSiteconso extends CertissimXMLElement
{

  public function __construct($ca = "", $nb = "", $datepremcmd = "", $datederncmd = "")
  {
    parent::__construct();

    $this->childCa($ca);
    $this->childNb($nb);
    $this->childDatepremcmd($datepremcmd);
    $this->childDatederncmd($datederncmd);
  }

}