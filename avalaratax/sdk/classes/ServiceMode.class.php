<?php
/**
 * ServiceMode.class.php
 */
 
/**
 * Specifies the ServiceMode.
 *
 * @see GetTaxRequest, GetTaxHistoryRequest
 * 
 * This is only supported by AvaLocal servers. It provides the ability to controls whether tax is calculated locally or remotely when using an AvaLocal server.
 * The default is Automatic which calculates locally unless remote is necessary for non-local addresses
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax 
 */

class ServiceMode extends Enum
{
     /**
     * Automated handling by local and/or remote server.
     */
    public static $Automatic = "Automatic";


    /**
     * AvaLocal server only. Lines requiring remote will not be calculated.
     */
    public static $Local = "Local";

    /**
     * All lines are calculated by AvaTax remote server.
     */
    public static $Remote = "Remote";
    
    public static function Values()
	{
		return array(
			ServiceMode::$Automatic,
			ServiceMode::$Local,
			ServiceMode::$Remote			
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}
?>