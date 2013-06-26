<?php

/**
 * Implements the tag <answer> from the response from Remote Control
 *
 * @author ESPIAU Nicolas
 */
class KwixoRemoteControlResponse extends KwixoDOMDocument{
  
  public function getNbAcks(){
    return $this->root->getAttribute('nb');
  }

  /**
   * returns an array of objects KwixoRemoteControlResponseAck
   * 
   * @return \KwixoRemoteControlResponseAck
   */
  public function getAcks() {
    $acks = array();

    foreach ($this->getElementsByTagName('ack') as $ack) {
      $acks[] = new KwixoRemoteControlResponseAck($ack->C14N());
    }

    return $acks;
  }

}