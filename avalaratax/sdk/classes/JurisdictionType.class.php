<?php
/**
 * JurisdictionType.class.php
 */
 
/**
 * A Jurisdiction Type describes the jurisdiction for which a particular tax is applied to a document.
 * Jurisdiction is determined by the GetTaxRequest#getDestinationAddress or GetTaxRequest#getOriginAddress of a GetTaxRequest. 
 * Multiple jurisdictions might be applied to a single Line during a tax calcuation.
 * Details are available in the TaxDetail of the GetTaxResult. 
 *
 * @see TaxDetail
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
*/
class JurisdictionType 
{
	/**
	 *  Unspecified Jurisdiction
	 *
	 * @var JurisdictionType
	 */
	public static $Composite	= 'Composite';
	
	/**
	 * State
	 *
	 * @var JurisdictionType
	 */
    public static $State	= 'State';
    
    /**
	 * County
	 *
	 * @var JurisdictionType
	 */
    public static $County	= 'County';
    
    /**
	 * City
	 *
	 * @var JurisdictionType
	 */
    public static $City		= 'City';
    
    /**
	 * Special Tax Jurisdiction
	 *
	 * @var JurisdictionType
	 */
    public static $Special	= 'Special';
/*
    
	public static function Values()
	{
		return array(
			JurisdictionType::$Composite,
			JurisdictionType::$State,
			JurisdictionType::$County,
			JurisdictionType::$City,
			JurisdictionType::$Special
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
	
	*/
	
}

?>