<?php
/**
 * CertificateImageGetRequest.class.php
 */

/**
 * Input for {@link CertificateImageGet}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateImageGetRequest {
  private $CompanyCode; // string
  private $AvaCertId; // string
  private $Format; // FormatType
  private $PageNumber; // int

  public function __construct()
  {
  	$this->Format=FormatType::$NULL;
  	$this->PageNumber=1;
  }
  
  public function setCompanyCode($value){$this->CompanyCode=$value;} // string

/**
 * The company code associated with a certificate record. 
 */
  public function getCompanyCode(){return $this->CompanyCode;} // string

  public function setAvaCertId($value){$this->AvaCertId=$value;} // string

/**
 * Unique identifier for the Certificate record. 
 */
  public function getAvaCertId(){return $this->AvaCertId;} // string

  public function setFormat($value){$this->Format=$value;} // FormatType

/**
 * Format in which the image needs to be exported. 
 */
  public function getFormat(){return $this->Format;} // FormatType

  public function setPageNumber($value){$this->PageNumber=$value;} // int

/**
 * Page number of of the certificate image. 
 */
  public function getPageNumber(){return $this->PageNumber;} // int

}

?>
