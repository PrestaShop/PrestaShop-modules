<?php

/**
 * Implements the service Sceau de Confiance including every webservices access methodes
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Sceau extends SceauService {

  /**
   * send an XML stream using sendrating.cgi and method POST and returns the answer of the script as a string
   *
   * @param XMLElement $xml
   * @return string
   */
  public function sendSendrating(SceauControl &$xml) {
    $this->addCrypt($xml);
    SceauLogger::insertLogSceau(__METHOD__, $xml->saveXML());
    $data = array(
        'SiteID' => $this->getSiteId(),
        'XMLInfo' => $xml->saveXML(),
        'CheckSum' => md5($xml->saveXML()),
    );
    $con = new SceauSocket($this->getUrlsendrating(), 'POST', $data);
    $res = $con->send();

    return $res;
  }

  /**
   * generates and returns the crypt value for the order $order
   *
   * @param XMLElement $order
   * @return string
   */
  public function generateCrypt(SceauControl $order) {

    $refid = $order->getOneElementByTagName('refid')->nodeValue;
    $timestamp = $order->getOneElementByTagName('ip')->getAttribute('timestamp');
    $email = $order->getOneElementByTagName('email')->nodeValue;
    $secure_string = $this->getAuthkey() . '_' . $refid . '+' . $timestamp . '=' . strtolower($email);
    $crypt_value = md5($secure_string);
    return $crypt_value;
  }

  /**
   * adds the element <crypt> into the stream if it has no element <crypt> already, do nothing otherwise
   *
   * @param XMLElement $order 
   */
  public function addCrypt(SceauControl &$order) {
    $crypt = $order->getOneElementByTagName('crypt');
    if (is_null($crypt)) {
      $order->createChild('crypt', $this->generateCrypt($order), array());
    } else {
      $crypt->nodeValue = $this->generateCrypt($order);
    }
  }

}