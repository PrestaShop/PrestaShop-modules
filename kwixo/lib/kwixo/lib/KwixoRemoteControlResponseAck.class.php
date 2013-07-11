<?php

/**
 * Implements an XML element <ack>, child of response to Remote Control WS : <answer>
 *
 * @author ESPIAU Nicolas
 */
class KwixoRemoteControlResponseAck extends KwixoDOMDocument{

  public function getTransactionID() {
    return $this->root->getAttribute('transactionid');
  }

  public function getChecksum() {
    return $this->root->getAttribute('checksum');
  }

  public function getValue() {
    return $this->root->nodeValue;
  }

  /**
   * returns true if the call failed, false otherwise
   *
   * @return bool
   */
  public function hasError() {
    return $this->root->hasAttribute('liberr');
  }

  /**
   * returns the error code
   *
   * @return string
   */
  public function getErrorCode() {
    return $this->root->getAttribute('coderr');
  }

  /**
   * returns the error label
   *
   * @return string
   */
  public function getError() {
    return $this->root->getAttribute('liberr');
  }

  /**
   * generates and returns a checksum
   * 
   * @return string
   */
  public function generateChecksum() {
    $md5 = new KwixoMD5();
    $kwixo = new Kwixo();
    $checksum = $md5->hash($kwixo->getAuthkey() . (string) $this->getTransactionID() . $this->root->nodeValue);

    return $checksum;
  }

  /**
   * returns true if the received checksum is valid, false otherwise
   *
   * @return bool
   */
  public function checksumIsValid() {
    //gets the received checksum
    $checksum = $this->getChecksum();
    //generates the waited checksum
    $waitedchecksum = $this->generateChecksum();
    //returns true if checksums match, false otherwise
    return $checksum == $waitedchecksum;
  }

}