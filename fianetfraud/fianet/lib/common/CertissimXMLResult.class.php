<?php

/**
 * Class for the Certissim services responses, including a magic method allowing developper to access the attributes directly
 * Ex. :
 * $result = new CertissimXMLResult($xml_string);
 * $date_attribute_value = $result->returnDate();
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class CertissimXMLResult extends CertissimXMLElement
{

  public function __call($name, array $params)
  {
    //fonction returnItem : retourne la valeur de l'attribute Item si existant, null sinon
    if (preg_match('#^return.+$#', $name))
    {
      $elementname = strtolower(preg_replace('#^return(.+)$#', '$1', $name));

      return array_key_exists($elementname, $this->getAttributes()) ? $this->getAttribute($elementname) : null;
    }

    return parent::__call($name, $params);
  }

}