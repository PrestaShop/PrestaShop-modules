<?php
/**
 * SeverityLevel.class.php
 */

/**
 * Severity of the result {@link Message}.
 *
 * Defines the constants used to specify SeverityLevel in {@link Message}
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */
 
class SeverityLevel extends Enum
{
    public static $Success = 'Success';
    public static $Warning = 'Warning';
    public static $Error = 'Error';
    public static $Exception = 'Exception';
 
	
	public static function Values()
	{
		return array(
			SeverityLevel::$Success,
			SeverityLevel::$Warning,
			SeverityLevel::$Error,
			SeverityLevel::$Exception
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>