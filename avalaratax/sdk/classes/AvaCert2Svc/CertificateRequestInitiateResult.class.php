<?php
/**
 * CertificateRequestInitiateResult.class.php
 */

/**
 * Contains the certificate request initiate operation result returned by {@link CertificateRequestInitiate}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequestInitiateResult extends BaseResult {
  private $TrackingCode; // string
  private $WizardLaunchUrl; // string
  private $RequestId; // string
  private $CustomerCode; // string

/**
 * TrackingCode indicates the unique Tracking Code of the Request. 
 */
  public function getTrackingCode(){return $this->TrackingCode;} // string

/**
 * WizardLaunchUrl indicates the unique tracking Url for the Request, that is used to launch the wizard. 
 */
  public function getWizardLaunchUrl(){return $this->WizardLaunchUrl;} // string

/**
 * Unique identifier for the Request record. 
 */
  public function getRequestId(){return $this->RequestId;} // string

/**
 * Customer identification code from client system. 
 */
  public function getCustomerCode(){return $this->CustomerCode;} // string

}

?>
