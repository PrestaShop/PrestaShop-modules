<?php
/**
 * TaxRequest.class.php
 */

/**
 * Abstract base class for all CancelTax,GetTaxHistory,PostTax, Service Requests.
 * Tax Requests require either a DocId, or CompanyCode, DocType, and DocCode.
 *
 * @see TaxServiceSoap
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class TaxRequest
{

    /**
    * @access public
    * @var string
    */
    protected $CompanyCode;         
    /**
    * Must be one of SalesOrder or SalesInvoice or PurchaseOrder or PurchaseInvoice or ReturnOrder or ReturnInvoice
    * Constants defined in DocumentType.
    *
    * @see DocumentType
    * @access public
    * @var string
    */
    protected $DocType;             
    /**
    * Invoice Number
    *
    * @access public
    * @var string
    */
    protected $DocCode;             

    /**
    * A unique document ID.
    * <p>
    * This is a unique AvaTax identifier for this document.  If known, the
    * <b>CompanyCode</b>, <b>DocCode</b>, and <b>DocType</b> are not needed.
    *
    * @access public
    * @var string
    */
    //protected $DocId;	
	
	/**
    * Sets the client application company reference code.
    * <br>If docId is specified, this is not needed.
    *
    * @param string
    */
	
	protected $HashCode;

    public function setCompanyCode($value) { $this->CompanyCode = $value; return $this; }

    /**
    * The original document's type, such as Sales Invoice or Purchase Invoice.
    *
    * @var string
    * @see DocumentType
    */
    public function setDocType($value) { DocumentType::Validate($value); $this->DocType=$value; return $this; }

    /**
    * Sets the Document Code, that is the internal reference code used by the client application.
    * <br>If docId is specified, this is not needed.
    *
    * @var string
    */
    public function setDocCode($value) { $this->DocCode = $value; return $this; }

    /**
    *  A unique document ID.
    * <p>
    * This is a unique AvaTax identifier for this document.  If known, the
    * <b>CompanyCode</b>, <b>DocCode</b>, and <b>DocType</b> are not needed.
    *
    * @var string
    * @see GetTaxResult#DocId
    */

    //public function setDocId($value) { $this->DocId = $value; return $this; }

    /**
    * Sets the hashCode value for this GetTaxRequest.
    * <p>
    * This should be computed by an SDK developer using some standard algorithm out of the content of the object. This value gets stored in the system and can be retrieved for the cross checking [Internal Reconciliation purpose].
    * See sample code for more details
    * </p>
    * @var int
    */

    public function setHashCode($value) { $this->HashCode = $value; return $this; }
    // Accessors
    /**
    * Gets the client application company reference code.    
    *
    * @return string
    */

    public function getCompanyCode() { return $this->CompanyCode; }


    /**
    * Gets the hashCode value for this GetTaxRequest.
    * <p>
    * This should be computed by an SDK developer using some standard algorithm out of the content of the object. This value gets stored in the system and can be retrieved for the cross checking [Internal Reconciliation purpose].
    * See sample code for more details
    *  </p>
    * @return int
    */

    public function getHashCode() { return $this->HashCode; }

    /**
    * The original document's type, such as Sales Invoice or Purchase Invoice.
    *
    * @return string
    * @see DocumentType
    */


    public function getDocType() { return $this->DocType; }

    /**
    * Gets the Document Code, that is the internal reference code used by the client application.
    * <br>If docId is specified, this is not needed.
    *
    * @return string
    */

    public function getDocCode() { return $this->DocCode; }

    /**
    *  A unique document ID.
    * <p>
    * This is a unique AvaTax identifier for this document.  If known, the
    * <b>CompanyCode</b>, <b>DocCode</b>, and <b>DocType</b> are not needed.
    *
    * @return string
    * @see GetTaxResult#DocId
    */

    //public function getDocId() { return $this->DocId; }
	
	public function __construct()
	{
		$this->DocType = DocumentType::$SalesOrder;  // this is right Document
		$this->HashCode= 0;
		
	}

}

?>