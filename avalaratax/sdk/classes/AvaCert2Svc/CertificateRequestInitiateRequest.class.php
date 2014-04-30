<?php
/**
 * CertificateRequestInitiateRequest.class.php
 */

/**
 * Input for {@link CertificateRequestInitiate}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequestInitiateRequest {
  private $CompanyCode; // string
  private $CustomerCode; // string
  private $CommunicationMode; // CommunicationMode
  private $SourceLocationCode; // string
  private $Type; // RequestType
  private $CustomMessage; // string
  private $LetterTemplate; // string
  private $IncludeCoverPage; // boolean
  private $CloseReason; // string
  private $RequestId; // string
 
  public function __construct()
  {		
		$this->CommunicationMode = CommunicationMode::$EMAIL;
		$this->Type=RequestType::$STANDARD;
  }
  
  public function setCompanyCode($value){$this->CompanyCode=$value;} // string

/**
 * Company Code of the company to which the customer belongs. 
 */
  public function getCompanyCode(){return $this->CompanyCode;} // string

  public function setCustomerCode($value){$this->CustomerCode=$value;} // string

/**
 * Customer identification code from client system. 
 */
  public function getCustomerCode(){return $this->CustomerCode;} // string

  public function setCommunicationMode($value){$this->CommunicationMode=$value;} // CommunicationMode

/**
 * CommunicationMode indicates the mode to use for communicating with the customer like Email, Mail, or Fax. 
 */
  public function getCommunicationMode(){return $this->CommunicationMode;} // CommunicationMode

  public function setSourceLocationCode($value){$this->SourceLocationCode=$value;} // string

/**
 * SourceLocationCode is the Source LocationCode for the request. If provided; the code must be one that exists for the Company. 
 */
  public function getSourceLocationCode(){return $this->SourceLocationCode;} // string

  public function setType($value){$this->Type=$value;} // RequestType

/**
 * Type indicates the type of the request to be initiated. 
 */
  public function getType(){return $this->Type;} // RequestType

  public function setCustomMessage($value){$this->CustomMessage=$value;} // string

/**
 * Custom message to be used for the request. 
 */
  public function getCustomMessage(){return $this->CustomMessage;} // string

  public function setLetterTemplate($value){$this->LetterTemplate=$value;} // string

/**
 * The name of the Letter Template to use for the correspondence. 
 */
  public function getLetterTemplate(){return $this->LetterTemplate;} // string

  public function setIncludeCoverPage($value){$this->IncludeCoverPage=$value;} // boolean

/**
 * Whether or not a fax cover sheet with a barcode will be attached to the correspondence. If null, the default is used (false). 
 */
  public function getIncludeCoverPage(){return $this->IncludeCoverPage;} // boolean

  public function setCloseReason($value){$this->CloseReason=$value;} // string

/**
 * Reason for closing the Request. 
 */
  public function getCloseReason(){return $this->CloseReason;} // string

  public function setRequestId($value){$this->RequestId=$value;} // string

/**
 * Unique identifier for the Request record. 
 */
  public function getRequestId(){return $this->RequestId;} // string

}

?>
