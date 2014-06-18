<?php
/**
 * CertificateGetRequest.class.php
 */

/**
 * Input for {@link CertificateGet}.
 * 
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateGetRequest {
  private $CompanyCode; // string
  private $CustomerCode; // string
  private $ModFromDate; // dateTime
  private $ModToDate; // dateTime

  public function __construct()
  {
	$dateTime=new DateTime();
    $dateTime->setDate(0001,01,01);
    $this->ModFromDate=$dateTime->format("Y-m-d");
        
    $dateTime->setDate(0001,01,01);
    $this->ModToDate=$dateTime->format("Y-m-d");
  }
	
  public function setCompanyCode($value){$this->CompanyCode=$value;} // string
 
/**
 * The company code associated with a certificate record. 
 */
  public function getCompanyCode(){return $this->CompanyCode;} // string

  public function setCustomerCode($value){$this->CustomerCode=$value;} // string

/**
 * The customer code associated with a certificate record. 
 */
  public function getCustomerCode(){return $this->CustomerCode;} // string

  public function setModFromDate($value){$this->ModFromDate=$value;} // dateTime

/**
 * The date from which the certificates needs to be fetched. 
 */
  public function getModFromDate(){return $this->ModFromDate;} // dateTime

  public function setModToDate($value){$this->ModToDate=$value;} // dateTime

/**
 * The date to which the certificates needs to be fetched. 
 */
  public function getModToDate(){return $this->ModToDate;} // dateTime

}

?>
