<?php
/**
 * InitiateExemptCertRequest.class.php
 */

/**
 * Data to pass to {@link AvaCertServiceSoap#InitiateExemptCert}.
 *
 * @see InitiateExemptCertResult
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class InitiateExemptCertRequest {
  private $Customer; // Customer
  private $LocationCode; // string
  private $CustomMessage; // string
  private $CommunicationMode; // CommunicationMode
  private $Type; // RequestType
  
  public function __construct()
  {		
		$this->CommunicationMode = CommunicationMode::$Email;
		$this->Type=RequestType::$STANDARD;
  }
	
  public function setCustomer($value){$this->Customer=$value;} // Customer
  public function getCustomer(){return $this->Customer;} // Customer

  public function setLocationCode($value){$this->LocationCode=$value;} // string
  public function getLocationCode(){return $this->LocationCode;} // string

  public function setCustomMessage($value){$this->CustomMessage=$value;} // string
  public function getCustomMessage(){return $this->CustomMessage;} // string

  public function setCommunicationMode($value){$this->CommunicationMode=$value;} // CommunicationMode
  public function getCommunicationMode(){return $this->CommunicationMode;} // CommunicationMode
  
  public function setType($value){$this->Type=$value;} // RequestType
  public function getType(){return $this->Type;} // RequestType
 }

?>
