<?php
/**
 * CertificateImageGetResult.class.php
 */

/**
 * Contains the get certificate image operation result returned by {@link CertificateImageGet}. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateImageGetResult extends BaseResult {
  private $AvaCertId; // string
  private $Image; // base64Binary

/**
 * Unique identifier for the Certificate record. 
 */
  public function getAvaCertId(){return $this->AvaCertId;} // string

/**
 * Certificate image. 
 */
  public function getImage(){return $this->Image;} // base64Binary

}

?>
