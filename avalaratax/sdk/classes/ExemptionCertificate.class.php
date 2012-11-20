<?php
/**
 * ExemptionCertificate.class.php
 */

/**
 * Contains exemption certificate data. Is part of the GetExemptionCertificatesResult result came from the GetExemptionCertificates.
 * @see GetExemptionCertificatesResult
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class ExemptionCertificate {
  private $AvaCertId; // string
  private $Jurisdictions; // ArrayOfJurisdiction
  private $CustomerCodes; // ArrayOfString
  private $CustomerType; // string
  private $LocationName; // string
  private $LocationCode; // string
  private $CertificateStatus; // CertificateStatus
  private $ReviewStatus; // ReviewStatus
  private $CreatedDate; // dateTime
  private $ModifiedDate; // dateTime
  private $ReceivedDate; // dateTime
  private $BusinessName; // string
  private $Address1; // string
  private $Address2; // string
  private $City; // string
  private $Region; // string
  private $Country; // string
  private $PostalCode; // string
  private $Phone; // string
  private $Email; // string
  private $SignedDate; // dateTime
  private $SignerName; // string
  private $SignerTitle; // string
  private $BusinessDescription; // string
  private $SellerPropertyDescription; // string
  private $CertificateUsage; // CertificateUsage
  private $IsPartialExemption; // boolean
  private $ExemptReasonCode; // string
  private $ExemptFormName; // string
  private $Custom1; // string
  private $Custom2; // string
  private $Custom3; // string

  public function setAvaCertId($value){$this->AvaCertId=$value;} // string
  public function getAvaCertId(){return $this->AvaCertId;} // string

  public function setJurisdictions($value){$this->Jurisdictions=$value;} // ArrayOfJurisdiction
  public function getJurisdictions(){return $this->Jurisdictions;} // ArrayOfJurisdiction

  public function setCustomerCodes($value){$this->CustomerCodes=$value;} // ArrayOfString
  public function getCustomerCodes(){return $this->CustomerCodes;} // ArrayOfString

  public function setCustomerType($value){$this->CustomerType=$value;} // string
  public function getCustomerType(){return $this->CustomerType;} // string

  public function setLocationName($value){$this->LocationName=$value;} // string
  public function getLocationName(){return $this->LocationName;} // string

  public function setLocationCode($value){$this->LocationCode=$value;} // string
  public function getLocationCode(){return $this->LocationCode;} // string

  public function setCertificateStatus($value){$this->CertificateStatus=$value;} // CertificateStatus
  public function getCertificateStatus(){return $this->CertificateStatus;} // CertificateStatus

  public function setReviewStatus($value){$this->ReviewStatus=$value;} // ReviewStatus
  public function getReviewStatus(){return $this->ReviewStatus;} // ReviewStatus

  public function setCreatedDate($value){$this->CreatedDate=$value;} // dateTime
  public function getCreatedDate(){return $this->CreatedDate;} // dateTime

  public function setModifiedDate($value){$this->ModifiedDate=$value;} // dateTime
  public function getModifiedDate(){return $this->ModifiedDate;} // dateTime

  public function setReceivedDate($value){$this->ReceivedDate=$value;} // dateTime
  public function getReceivedDate(){return $this->ReceivedDate;} // dateTime

  public function setBusinessName($value){$this->BusinessName=$value;} // string
  public function getBusinessName(){return $this->BusinessName;} // string

  public function setAddress1($value){$this->Address1=$value;} // string
  public function getAddress1(){return $this->Address1;} // string

  public function setAddress2($value){$this->Address2=$value;} // string
  public function getAddress2(){return $this->Address2;} // string

  public function setCity($value){$this->City=$value;} // string
  public function getCity(){return $this->City;} // string

  public function setRegion($value){$this->Region=$value;} // string
  public function getRegion(){return $this->Region;} // string

  public function setCountry($value){$this->Country=$value;} // string
  public function getCountry(){return $this->Country;} // string

  public function setPostalCode($value){$this->PostalCode=$value;} // string
  public function getPostalCode(){return $this->PostalCode;} // string

  public function setPhone($value){$this->Phone=$value;} // string
  public function getPhone(){return $this->Phone;} // string

  public function setEmail($value){$this->Email=$value;} // string
  public function getEmail(){return $this->Email;} // string

  public function setSignedDate($value){$this->SignedDate=$value;} // dateTime
  public function getSignedDate(){return $this->SignedDate;} // dateTime

  public function setSignerName($value){$this->SignerName=$value;} // string
  public function getSignerName(){return $this->SignerName;} // string

  public function setSignerTitle($value){$this->SignerTitle=$value;} // string
  public function getSignerTitle(){return $this->SignerTitle;} // string

  public function setBusinessDescription($value){$this->BusinessDescription=$value;} // string
  public function getBusinessDescription(){return $this->BusinessDescription;} // string

  public function setSellerPropertyDescription($value){$this->SellerPropertyDescription=$value;} // string
  public function getSellerPropertyDescription(){return $this->SellerPropertyDescription;} // string

  public function setCertificateUsage($value){$this->CertificateUsage=$value;} // CertificateUsage
  public function getCertificateUsage(){return $this->CertificateUsage;} // CertificateUsage

  public function setIsPartialExemption($value){$this->IsPartialExemption=$value;} // boolean
  public function getIsPartialExemption(){return $this->IsPartialExemption;} // boolean

  public function setExemptReasonCode($value){$this->ExemptReasonCode=$value;} // string
  public function getExemptReasonCode(){return $this->ExemptReasonCode;} // string

  public function setExemptFormName($value){$this->ExemptFormName=$value;} // string
  public function getExemptFormName(){return $this->ExemptFormName;} // string

  public function setCustom1($value){$this->Custom1=$value;} // string
  public function getCustom1(){return $this->Custom1;} // string

  public function setCustom2($value){$this->Custom2=$value;} // string
  public function getCustom2(){return $this->Custom2;} // string

  public function setCustom3($value){$this->Custom3=$value;} // string
  public function getCustom3(){return $this->Custom3;} // string

}

?>
