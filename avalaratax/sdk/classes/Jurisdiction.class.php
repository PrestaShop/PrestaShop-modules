<?php
/**
 * Jurisdiction.class.php
 */

/**
 * Contains jurisdiction data.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */
class Jurisdiction {
  private $JurisdictionCode; // string
  private $Country; // string
  private $ExpiryDate; // dateTime
  private $DoesNotExpire; // boolean
  private $PermitNumbers; // ArrayOfString

  public function setJurisdictionCode($value){$this->JurisdictionCode=$value;} // string
  public function getJurisdictionCode(){return $this->JurisdictionCode;} // string

  public function setCountry($value){$this->Country=$value;} // string
  public function getCountry(){return $this->Country;} // string

  public function setExpiryDate($value){$this->ExpiryDate=$value;} // dateTime
  public function getExpiryDate(){return $this->ExpiryDate;} // dateTime

  public function setDoesNotExpire($value){$this->DoesNotExpire=$value;} // boolean
  public function getDoesNotExpire(){return $this->DoesNotExpire;} // boolean

  public function setPermitNumbers($value){$this->PermitNumbers=$value;} // ArrayOfString
  public function getPermitNumbers(){return $this->PermitNumbers;} // ArrayOfString

}

?>
