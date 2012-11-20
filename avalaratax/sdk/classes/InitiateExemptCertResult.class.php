<?php
/**
 * InitiateExemptCertResult.class.php
 */

/**
 * Result data returned from {@link AvaCertServiceSoap#InitiateExemptCert}.
 *
 * @see InitiateExemptCertRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */

class InitiateExemptCertResult extends BaseResult {
  private $TrackingCode; // string
  private $WizardLaunchUrl; // string  

  public function getTrackingCode(){return $this->TrackingCode;} // string

  public function getWizardLaunchUrl(){return $this->WizardLaunchUrl;} // string  
}

?>
