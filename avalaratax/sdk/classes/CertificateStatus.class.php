<?php
/**
 * CertificateStatus.class.php
 */

/**
 * CertificateStatus indicates the status for the Certificate record.
 * @see ExemptionCertificate
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
*/
class CertificateStatus extends Enum
{	    
     /**
	 *  The certificate is active with images received.
	 *
	 * @var CertificateStatus
	 */
    public static $ACTIVE	= 'ACTIVE';
    
    /**
	 *  The certificate has been voided from active use.
	 *
	 * @var CertificateStatus
	 */
    public static $VOID	= 'VOID';
       
    /**
	 *  The certificate does not yet have all of its images received.
	 *
	 * @var CertificateStatus
	 */
    public static $INCOMPLETE	= 'INCOMPLETE';
    
	public static function Values()
	{
		return array(
			CertificateStatus::$ACTIVE,
			CertificateStatus::$VOID,
			CertificateStatus::$INCOMPLETE	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }

}

?>