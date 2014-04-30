<?php
/**
 * DocumentType.class.php
 */
 
 /**
 * The document type specifies the category of the document and affects how the document
 * is treated after a tax calculation. Specified when constructing a {@link GetTaxRequest}.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */


class DocumentType extends Enum
{

	/**
	 * Sales Order, estimate or quote.
	 *
	 * @var DocumentType
	 */
	public static $SalesOrder		= 'SalesOrder';
	
	/**
	 *   The document is a permanent invoice; document and tax calculation results are saved in the tax history.
	 *
	 * @var DocumentType
	 */
    public static $SalesInvoice		= 'SalesInvoice';
    
    /**
	 *  Purchase order, estimate, or quote.
	 *
	 * @var DocumentType
	 */
    public static $PurchaseOrder	= 'PurchaseOrder';
    
    /**
	 *  The document is a permanent invoice; document and tax calculation results are saved in the tax history.
	 *
	 * @var DocumentType
	 */
    public static $PurchaseInvoice	= 'PurchaseInvoice';
    
    /**
	 *Sales Return Order.
	 *
	 * @var DocumentType
	 */
    public static $ReturnOrder		= 'ReturnOrder';
    
    /**
	 * The document is a permanent sales return invoice; document and tax calculation results are saved in the tax history GetTaxResult will return with a DocStatus of Saved.
	 *
	 * @var DocumentType
	 */
    public static $ReturnInvoice	= 'ReturnInvoice';
    
    /**
	 * This will return all types of documents.
	 *
	 * @var DocumentType
	 */
    public static $Any	= 'Any';
    
	public static function Values()
	{
		return array(
			DocumentType::$SalesOrder,
			DocumentType::$SalesInvoice,
			DocumentType::$PurchaseOrder,
			DocumentType::$PurchaseInvoice,
			DocumentType::$ReturnOrder,
			DocumentType::$ReturnInvoice,	
			DocumentType::$Any	
		);
	}
	// Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
	
   
	
	
}

?>