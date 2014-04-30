<?php
/**
 * DetailLevel.class.php
 */
 
/**
 * Specifies the level of tax detail to return to the client application following a tax calculation.
 * @see GetTaxRequest, GetTaxHistoryRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */


class DetailLevel extends Enum
{
   /**
     * Reserved for future use.
     */
	public static $Summary		= 'Summary';
	
	/**
     *  Document ({@link GetTaxResult}) level details; {@link ArrayOfTaxLine} will not be returned.
     */
    public static $Document		= 'Document';
	
	/**
     *  Line level details (includes Document details). {@link ArrayOfTaxLine} will
     * be returned but {@link ArrayOfTaxDetail} will not be returned.
     */     
    public static $Line			= 'Line';
	
	/**
     *  Tax jurisdiction level details (includes Document, {@link ArrayOfTaxLine},
     * and {@link ArrayOfTaxDetail})
     */
    public static $Tax			= 'Tax';
    public static $Diagnostic	= 'Diagnostic';

    
	public static function Values()
	{
		return array(
			DetailLevel::$Summary,
			DetailLevel::$Document,
			DetailLevel::$Line,
			DetailLevel::$Tax,
			DetailLevel::$Diagnostic
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>