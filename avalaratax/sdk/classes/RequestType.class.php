<?php
/**
 * RequestType.class.php
 */

/**
 * RequestType indicates the type of the request to be initiated. 
 * @see InitiateExemptCertRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
*/
class RequestType extends Enum
{	    
     /**
	 *  Standard sends correspondences and follow ups related to the Request to the associated Customer.
	 *
	 * @var RequestType
	 */
    public static $STANDARD	= 'STANDARD';
    
    /**
	 *  Direct does not send any correspondence or follow ups related to the Request to the associated Customer.
	 *
	 * @var RequestType
	 */
    public static $DIRECT	= 'DIRECT';
    
	public static function Values()
	{
		return array(
			RequestType::$STANDARD,
			RequestType::$DIRECT	
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }

}

?>