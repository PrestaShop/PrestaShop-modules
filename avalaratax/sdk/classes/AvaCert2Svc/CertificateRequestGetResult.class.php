<?php
/**
 * CertificateRequestGetResult.class.php
 */

/**
 * Contains the get certificate request operation result returned by {@link CertificateRequestGet}. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequestGetResult extends BaseResult {
  private $CertificateRequests; // ArrayOfCertificateRequest

/**
 * CertificateRequests contains collection of certificate requests. 
 */
  public function getCertificateRequests()
  {
  	 if(isset($this->CertificateRequests->CertificateRequest))
     {
     	return EnsureIsArray($this->CertificateRequests->CertificateRequest);
     }
     else
     {
     	return null; 
     }  	
  } // ArrayOfCertificateRequest

}

?>