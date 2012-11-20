<?php
/**
 * GetExemptionCertificatesRequest.class.php
 */

/**
 * Data to pass to {@link AvaCertServiceSoap#GetExemptionCertificates}.
 *
 * @see GetExemptionCertificatesResult
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class GetExemptionCertificatesRequest {
  private $CustomerCode; // string
  private $FromDate; // dateTime
  private $ToDate; // dateTime
  private $Region; // string
  private $CompanyCode; // string

  public function __construct()
	{
		$dateTime=new DateTime();
        $dateTime->setDate(0001,01,01);
        $this->FromDate=$dateTime->format("Y-m-d");
        
        $dateTime->setDate(0001,01,01);
        $this->ToDate=$dateTime->format("Y-m-d");
	}
        
  public function setCustomerCode($value){$this->CustomerCode=$value;} // string
  public function getCustomerCode(){return $this->CustomerCode;} // string

  public function setFromDate($value){$this->FromDate=$value;} // dateTime
  public function getFromDate(){return $this->FromDate;} // dateTime

  public function setToDate($value){$this->ToDate=$value;} // dateTime
  public function getToDate(){return $this->ToDate;} // dateTime

  public function setRegion($value){$this->Region=$value;} // string
  public function getRegion(){return $this->Region;} // string

  public function setCompanyCode($value){$this->CompanyCode=$value;} // string
  public function getCompanyCode(){return $this->CompanyCode;} // string

}

?>
