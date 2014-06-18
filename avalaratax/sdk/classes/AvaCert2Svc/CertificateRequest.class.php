<?php
/**
 * CertificateRequest.class.php
 */

/**
 * Contains certificate request data.  Is part of the {@link CertificateRequestGetResult} result came from the {@link CertificateRequestGet}. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequest {
  private $RequestId; // string
  private $TrackingCode; // string
  private $SourceLocationCode; // string
  private $RequestDate; // dateTime
  private $CustomerCode; // string
  private $CreatorName; // string
  private $LastModifyDate; // dateTime
  private $RequestStatus; // CertificateRequestStatus
  private $RequestStage; // CertificateRequestStage
  private $CommunicationMode; // CommunicationMode

/**
 * Unique identifier for the certificate request record. 
 */
  public function getRequestId(){return $this->RequestId;} // string

/**
 * Unique Tracking Code for the certificate request record. 
 */
  public function getTrackingCode(){return $this->TrackingCode;} // string

/**
 * Source location code for the certificate record (the client location responsible for tracking the certificate). 
 */
  public function getSourceLocationCode(){return $this->SourceLocationCode;} // string

/**
 * Request date of the certificate request record. 
 */
  public function getRequestDate(){return $this->RequestDate;} // dateTime

/**
 * Customer identification code for the customer associated with the certificate request record. 
 */
  public function getCustomerCode(){return $this->CustomerCode;} // string

/**
 * CreatorName the certificate request record. 
 */
  public function getCreatorName(){return $this->CreatorName;} // string

/**
 * Last modification date of the certificate request record. 
 */
  public function getLastModifyDate(){return $this->LastModifyDate;} // dateTime

/**
 * Request status for the certificate request record. 
 */
  public function getRequestStatus(){return $this->RequestStatus;} // CertificateRequestStatus

/**
 * Request stage for the certificate request record. 
 */
  public function getRequestStage(){return $this->RequestStage;} // CertificateRequestStage

/**
 * CommunicationMode for the certificate request record. 
 */
  public function getCommunicationMode(){return $this->CommunicationMode;} // CommunicationMode

}

?>
