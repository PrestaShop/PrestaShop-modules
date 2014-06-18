<?php
/**
 * CertificateRequestStage.class.php
 */

/**
 * CertificateStatus indicates the current stage of the Request.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CertificateRequestStage extends Enum {

/**
 * Request has been initiated; correspondence has been sent to the associated Customer. 
 */
  public static $REQUESTINITIATED = 'REQUESTINITIATED';

/**
 * Customer has responded to the correspondence. 
 */
  public static $CUSTOMERRESPONDED = 'CUSTOMERRESPONDED';

/**
 * Customer has provided a Certificate. 
 */
  public static $CERTIFICATERECEIVED = 'CERTIFICATERECEIVED';

	public static function Values()
	{
		return array(
			CertificateRequestStage::$REQUESTINITIATED,
			CertificateRequestStage::$CUSTOMERRESPONDED,
			CertificateRequestStage::$CERTIFICATERECEIVED	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>
