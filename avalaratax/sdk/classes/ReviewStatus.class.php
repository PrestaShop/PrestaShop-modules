<?php
/**
 * ReviewStatus.class.php
 */

/**
 * ReviewStatus indicates the review status for the Certificate record.
 * @see ExemptionCertificate
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
*/
class ReviewStatus extends Enum
{	    
     /**
	 *  The certificate has not yet been reviewed.
	 *
	 * @var ReviewStatus
	 */
    public static $PENDING	= 'PENDING';
    
    /**
	 *  The certificate was accepted during review.
	 *
	 * @var ReviewStatus
	 */
    public static $ACCEPTED	= 'ACCEPTED';
       
    /**
	 *  The certificate was rejected during review.
	 *
	 * @var ReviewStatus
	 */
    public static $REJECTED	= 'REJECTED';
    
	public static function Values()
	{
		return array(
			ReviewStatus::$PENDING,
			ReviewStatus::$ACCEPTED,
			ReviewStatus::$REJECTED	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }

}

?>