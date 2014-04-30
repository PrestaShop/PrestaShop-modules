<?php
/**
 * CertificateUsage.class.php
 */

/**
 * CertificateUsage indicates the usage type for the Certificate record.
 * @see ExemptionCertificate
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
*/
class CertificateUsage extends Enum
{	    
     /**
	 *  The certificate may be used multiple times.
	 *
	 * @var CertificateUsage
	 */
    public static $BLANKET	= 'BLANKET';
    
    /**
	 *  The certificate may only be used for a single transaction.
	 *
	 * @var CertificateUsage
	 */
    public static $SINGLE	= 'SINGLE';
       
    /**
	 *  The value has not been set.
	 *
	 * @var CertificateUsage
	 */
    public static $NULL	= 'NULL';
    
	public static function Values()
	{
		return array(
			CertificateUsage::$BLANKET,
			CertificateUsage::$SINGLE,
			CertificateUsage::$NULL	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }

}

?>