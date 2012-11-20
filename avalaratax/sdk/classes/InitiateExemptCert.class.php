<?php
/**
 * InitiateExemptCert.class.php
 */

/**
 * Initiates the request for an exemption certificate with AvaCert. It can also add or update the exempt customer record.  
 * And returns the result of operation in a InitiateExemptCertResult object.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class InitiateExemptCert {
  private $InitiateExemptCertRequest; // InitiateExemptCertRequest

  public function setInitiateExemptCertRequest($value){$this->InitiateExemptCertRequest=$value;} // InitiateExemptCertRequest
  public function getInitiateExemptCertRequest(){return $this->InitiateExemptCertRequest;} // InitiateExemptCertRequest

}

?>
