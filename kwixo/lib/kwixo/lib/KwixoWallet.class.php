<?php

/**
 * Class for the <wallet> element
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoWallet extends KwixoXMLElement {
  const WALLET_VERSION = "1.0";

  public function __construct() {
    parent::__construct('wallet');
  }

  public function addCrypt($crypt, $cryptversion) {
    return $this->createChild('crypt', $crypt, array('version'=>$cryptversion));
  }

}