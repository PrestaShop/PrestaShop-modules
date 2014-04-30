<?php
/**
 * AddressType.class.php
 */

/**
 * The type of the address(es) returned in the validation result.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 * 
 */

class AddressType extends Enum
{
	public static $FirmOrCompany 	= 'F';
	public static $GeneralDelivery 	= 'G';
	public static $HighRise         = 'H';
    public static $POBox            = 'P';
    public static $RuralRoute       = 'R';
    public static $StreetOrResidential = 'S';
    
	public static function Values()
	{
		return array(
            'FirmOrCompany'         => AddressType::$FirmOrCompany,
            'GeneralDelivery'       => AddressType::$GeneralDelivery,
            'HighRise'              => AddressType::$HighRise,
            'POBox'                 => AddressType::$POBox,
            'RuralRoute'            => AddressType::$RuralRoute,
            'StreetOrResidential'   => AddressType::$StreetOrResidential
		);
	}

    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>
