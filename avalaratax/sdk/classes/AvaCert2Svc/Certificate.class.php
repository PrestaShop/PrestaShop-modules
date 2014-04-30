<?php
/**
 * Certificate.class.php
 */

/**
 * Contains exemption certificate data. Is part of the {@link CertificateGetResult} result came from the {@link CertificateGet}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class Certificate {
  private $AvaCertId; // string
  private $CertificateJurisdictions; // ArrayOfCertificateJurisdiction
  private $CustomerCodes; // ArrayOfString
  private $SourceLocationName; // string
  private $SourceLocationCode; // string
  private $CertificateStatus; // CertificateStatus
  private $ReviewStatus; // ReviewStatus
  private $RejectionReasonCode; // string
  private $RejectionReasonDetailCode; // string
  private $RejectionReasonCustomText; // string
  private $CreatedDate; // dateTime
  private $LastModifyDate; // dateTime
  private $DocReceivedDate; // dateTime
  private $BusinessName; // string
  private $Address1; // string
  private $Address2; // string
  private $City; // string
  private $State; // string
  private $Country; // string
  private $Zip; // string
  private $Phone; // string
  private $Email; // string
  private $SignerName; // string
  private $SignerTitle; // string
  private $SignedDate; // dateTime
  private $BusinessDescription; // string
  private $SellerPropertyDescription; // string
  private $CertificateUsage; // CertificateUsage
  private $IsPartialExemption; // boolean
  private $ExemptReasonCode; // string
  private $ExemptFormName; // string
  private $Custom1; // string
  private $Custom2; // string
  private $Custom3; // string
  private $PageCount; // int

/**
 * Unique identifier for the Certificate record. 
 */
  public function getAvaCertId(){return $this->AvaCertId;} // string

/**
 * CertificateJurisdictions contains the details of Jurisdiction. 
 */
  public function getCertificateJurisdictions(){return $this->CertificateJurisdictions;} // ArrayOfCertificateJurisdiction

/**
 * Customer identification codes for the customer associated with the Certificate record. 
 */  
  public function getCustomerCodes(){return $this->CustomerCodes;} // ArrayOfString

/**
 * Source location display name for the Certificate record (the client location responsible for tracking the certificate). 
 */
  public function getSourceLocationName(){return $this->SourceLocationName;} // string

/**
 * Source location code for the Certificate record (the client location responsible for tracking the certificate). 
 */
  public function getSourceLocationCode(){return $this->SourceLocationCode;} // string
  
/**
 * Status for the Certificate record. 
 */
  public function getCertificateStatus(){return $this->CertificateStatus;} // CertificateStatus
  
/**
 * Review status for the Certificate record. 
 */
  public function getReviewStatus(){return $this->ReviewStatus;} // ReviewStatus
  
/**
 * Reason for rejection of a certificate when ReviewStatus of the Certificate record is REJECTED. 
 */
  public function getRejectionReasonCode(){return $this->RejectionReasonCode;} // string

/**
 * Details about the reason for rejection of a certificate when ReviewStatus of the Certificate record is REJECTED. 
 */
  public function getRejectionReasonDetailCode(){return $this->RejectionReasonDetailCode;} // string

/**
 * Custom Reason or details about a reason for rejection of a certificate provided by the user when ReviewStatus of the Certificate record is REJECTED. 
 */
  public function getRejectionReasonCustomText(){return $this->RejectionReasonCustomText;} // string

/**
 * Creation date of the Certificate record. 
 */
  public function getCreatedDate(){return $this->CreatedDate;} // dateTime

/**
 * Last modification date of the Certificate record. 
 */
  public function getLastModifyDate(){return $this->LastModifyDate;} // dateTime

/**
 * Date of the most recently received image content for the Certificate record (usually via fax). 
 */
  public function getDocReceivedDate(){return $this->DocReceivedDate;} // dateTime

/**
 * Exempt customer business name for the Certificate record. 
 */
  public function getBusinessName(){return $this->BusinessName;} // string

/**
 * Exempt customer address1 field for the Certificate record. 
 */
  public function getAddress1(){return $this->Address1;} // string

/**
 * Exempt customer address2 field for the Certificate record. 
 */
  public function getAddress2(){return $this->Address2;} // string

/**
 * Exempt customer city address field for the Certificate record. 
 */
  public function getCity(){return $this->City;} // string

/**
 * Exempt customer state address field for the Certificate record. 
 */
  public function getState(){return $this->State;} // string

/**
 * Exempt customer country address field for the Certificate record. 
 */
  public function getCountry(){return $this->Country;} // string

/**
 * Exempt customer US zip or zip+4 code (or CA postal code) address field for the Certificate record. 
 */
  public function getZip(){return $this->Zip;} // string

/**
 * Exempt customer phone number field for the Certificate record. 
 */
  public function getPhone(){return $this->Phone;} // string

/**
 * Exempt customer email address field for the Certificate record. 
 */
  public function getEmail(){return $this->Email;} // string

/**
 * Exempt customer signer name field for the Certificate record. 
 */
  public function getSignerName(){return $this->SignerName;} // string

/**
 * Exempt customer signer title field for the Certificate record. 
 */
  public function getSignerTitle(){return $this->SignerTitle;} // string

/**
 * Effective date (or the actual signature date) of the Certificate record. 
 */
  public function getSignedDate(){return $this->SignedDate;} // dateTime

/**
 * Exempt customer business description field for the Certificate record. 
 */
  public function getBusinessDescription(){return $this->BusinessDescription;} // string

/**
 * Seller property description the exempt customer selected for the Certificate record. 
 */
  public function getSellerPropertyDescription(){return $this->SellerPropertyDescription;} // string

/**
 * Usage type for the Certificate record. 
 */
  public function getCertificateUsage(){return $this->CertificateUsage;} // CertificateUsage

/**
 * Whether the Certificate record is considered "partially exempt". 
 */
  public function getIsPartialExemption(){return $this->IsPartialExemption;} // boolean

/**
 * Client-specified exemption reason code for the Certificate record. 
 */
  public function getExemptReasonCode(){return $this->ExemptReasonCode;} // string
  
/**
 * Name of the state-issued form for the Certificate record; either a system-defined code name or a client-specified custom name. 
 */
  public function getExemptFormName(){return $this->ExemptFormName;} // string
  
/**
 * (Optional) Client-specified value for custom Certificate field 1, if enabled. 
 */
  public function getCustom1(){return $this->Custom1;} // string
  
/**
 * (Optional) Client-specified value for custom Certificate field 2, if enabled. 
 */
  public function getCustom2(){return $this->Custom2;} // string
  
/**
 * (Optional) Client-specified value for custom Certificate field 3, if enabled. 
 */
  public function getCustom3(){return $this->Custom3;} // string
  
/**
 * Number of pages in the Certificate record. 
 */
  public function getPageCount(){return $this->PageCount;} // int

}

?>