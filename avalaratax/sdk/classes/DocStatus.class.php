<?php
/**
 * DocStatus.class.php
 */

/**
 * The document's status is returned in the GetTaxResult (except for <b>DocStatus::$Any</b>)
 * and indicates the state of the document in tax history.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */


class DocStatus extends Enum
{

	/**
	 * A temporary document not saved (DocumentType was SalesOrder, PurchaseOrder, ReturnOrder)
	 *
	 * @var unknown_type
	 */
	public static $Temporary= 'Temporary';
	
	/**
	 *  A saved document (DocumentType was SalesInvoice, PurchaseInvoice, ReturnInvoice) ready to be posted.
	 *
	 * @var DocStatus
	 */
    public static $Saved	= 'Saved';
    
    /**
	 *  A posted document (not committed).
	 *
	 * @var DocStatus
	 */
    public static $Posted	= 'Posted';
    
    /**
	 *  A posted document that has been committed.
	 *
	 * @var DocStatus
	 */
    public static $Committed	= 'Committed';
    
    /**
	 *  A committed document that has been cancelled.
	 *
	 * @var DocStatus
	 */
    public static $Cancelled	= 'Cancelled';
    
    /**
	 * Enter description here...
	 *
	 * @var DocStatus
	 */
    public static $Adjusted	= 'Adjusted';
    
    /**
	 * Any status (used for searching)
	 *
	 * @var DocStatus
	 */
	public static $Any		= 'Any';

    
	public static function Values()
	{
		return array(
			DocStatus::$Temporary,
			DocStatus::$Saved,
			DocStatus::$Posted,
			DocStatus::$Committed,
			DocStatus::$Cancelled,
			DocStatus::$Adjusted,
			DocStatus::$Any		
		);
	}
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }

}

?>