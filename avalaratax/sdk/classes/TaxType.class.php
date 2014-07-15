<?php
/**
 * TaxType.class.php
 */

/**
 * The Type of the tax.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
class TaxType// extends Enum
{
	public static $Sales	= 'Sales';
	public static $Use		= 'Use';
	public static $ConsumerUse	= 'ConsumerUse';
	public static $Output		= 'Output';
	public static $Input		= 'Input';
	public static $Nonrecoverable		= 'Nonrecoverable';
	public static $Fee		= 'Fee';
    public static $Rental		= 'Rental';
	/*
    public static function Values()
	{
		return array(
			$TaxType::$Sales,
			$TaxType::$Use,
			$TaxType::$ConsumerUse,
       		$TaxType::$Output,
			$TaxType::$Input,
			$TaxType::$Nonrecoverable,
			$TaxType::$Fee,
            $TaxTpe::$Rental
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
	
	*/
	
}

	

?>