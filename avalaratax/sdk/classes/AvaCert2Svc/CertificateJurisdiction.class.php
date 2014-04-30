<?php
/**
 * CertificateJurisdiction.class.php
 */

/**
 * Contains jurisdiction data. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateJurisdiction {
  private $Jurisdiction; // string
  private $Country; // string
  private $ExpiryDate; // dateTime
  private $DoesNotExpire; // boolean
  private $PermitNumbers; // ArrayOfString

/**
 * Jurisdiction code for the Jurisdiction record. 
 */
  public function getJurisdiction(){return $this->Jurisdiction;} // string

/**
 * Country code for the Jurisdiction record (ISO-3166-1-alpha-2 Country Code). 
 */
  public function getCountry(){return $this->Country;} // string

/**
 * Expiration date for the Jurisdiction record. 
 */
  public function getExpiryDate(){return $this->ExpiryDate;} // dateTime

/**
 * Whether the Jurisdiction can expire; a Boolean flag with the following semantics: true: the Jurisdiction record never expires (regardless of ExpiryDate) false: the Jurisdiction record expires based on ExpiryDate 
 */
  public function getDoesNotExpire(){return $this->DoesNotExpire;} // boolean

/**
 * The exempt customer permit number(s) for the Jurisdiction record (a comma separated list if more than a single permit number exists for the Jurisdiction record). 
 */
  public function getPermitNumbers(){return $this->PermitNumbers;} // ArrayOfString

}

?>
