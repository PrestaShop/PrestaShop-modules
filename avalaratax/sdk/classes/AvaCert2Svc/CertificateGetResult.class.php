<?php
/**
 * CertificateGetResult.class.php
 */

/**
 * Contains the get exemption certificates operation result returned by {@link CertificateGet}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateGetResult extends BaseResult {
  private $Certificates; // ArrayOfCertificate

/**
 * Certificates contains collection of exemption certificate records. 
 */
  public function getCertificates()
  {
	if(isset($this->Certificates->Certificate))
     {
     	return EnsureIsArray($this->Certificates->Certificate);
     }
     else
     {
     	return null; 
     }  	  	
  } // ArrayOfCertificate

}

?>
