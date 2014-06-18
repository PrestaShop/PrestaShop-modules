<?php
/**
 * Line.class.php
 */

/**
 * A single line within a document containing data used for calculating tax. 
 *
 * @see GetTaxRequest
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
 
class Line
{
    private $No;                  //string  // line Number of invoice
    private $OriginCode;          //string  Line#getOriginAddress.
    private $DestinationCode;     //string  Line#getDestinationAddress.
	private $ItemCode;            //string
	private $Description;         //string
    private $TaxCode;             //string
    private $Qty;                 //decimal
    private $Amount;              //decimal // TotalAmmount 
    private $Discounted;          //boolean  is discount applied to this item
    private $RevAcct;             //string
    private $Ref1;                //string
    private $Ref2;                //string
    private $ExemptionNo;         //string	//zero tax will result if filled in
    private $CustomerUsageType;   //string
    private $BatchCode;				//string
    
	private $TaxOverride;	//TaxOverride
	private $OriginAddress;			//Address
	private $DestinationAddress;		//Address
	private $TaxIncluded;		//boolean



	public function __construct($no=1,$qty=1,$amount=100.00)
	{
		$this->No=$no;
		$this->Qty=$qty;
		$this->Amount=$amount;

		
		$this->Discounted=false;
		
		
	}	
	

/*	
 * Mutator
 * @access public
 * @param integer
 */
 
	/**
	 * Line Number.
	 *
	 * @param string $value	 
	 */
	public function setNo($value) { $this->No = $value; return $this; }								//string  // line Number of invoice
	
	/**
	 * Item Code (SKU)
	 *
	 * @param string $value	 
	 */
	public function setItemCode($value) { $this->ItemCode = $value; return $this; }			//string
	
	/**
	 * Sets the description which defines the description for the product or item.
	 *
	 * @param string $value	 
	 */
	public function setDescription($value) { $this->Description = $value; return $this; }	//string
    
	/**
	 *  System or Custom Tax Code.
	 *
	 * @param string $value	 
	 */
	public function setTaxCode($value) { $this->TaxCode = $value; return $this; }            //string
	
	/**
	 * Revenue Account.
	 *
	 * @param string $value	 
	 */
	public function setRevAcct($value) { $this->RevAcct = $value; return $this; }            //string
    
	/**
	 * Client specific reference field.
	 *
	 * @param string $value	 
	 */
	public function setRef1($value) { $this->Ref1 = $value; return $this; }					//string
    
	/**
	 * Client specific reference field.
	 *
	 * @param string $value	 
	 */
	public function setRef2($value) { $this->Ref2 = $value; return $this; }							//string
    
	/**
	 * Exemption number for this line 
	 *
	 * @param string $value	 
	 */
	public function setExemptionNo($value) { $this->ExemptionNo = $value; return $this; }			//string	//zero tax will result if filled in
    
	 /**
     * The client application customer or usage type.
     * <p>
     * This is used to determine the exempt status of the transaction based on the exemption tax rules for the 
     * jurisdictions involved.  This may also be set at the line level.
     * </p>
     * <p>
     * The standard values for the CustomerUsageType (A-L).<br/>
        A – Federal Government<br/>
        B – State/Local Govt.<br/>
        C – Tribal Government<br/>
        D – Foreign Diplomat<br/>
        E – Charitable Organization<br/>
        F – Religious/Education<br/>
        G – Resale<br/>
        H – Agricultural Production<br/>
        I – Industrial Prod/Mfg.<br/>
        J – Direct Pay Permit<br/>
        K – Direct Mail<br/>
        L - Other<br/>
     * </p>
     * @param string $value
     */
	public function setCustomerUsageType($value) { $this->CustomerUsageType = $value; return $this; }   //string
    
	/**
	 * Enter description here...
	 *
	 * @param string $value	 
	 */
	public function setBatchCode($value) { $this->BatchCode = $value; return $this; }					//string	
	
    /**
	 * The quantity represented by this line. 
	 *
	 * @param string $value	 
	 */
	public function setQty($value) { $this->Qty = $value; return $this; }					//decimal
    
	/**
	 * The total amount for this line item (Qty * UnitPrice). 
	 *
	 * @param string $value	 
	 */
	public function setAmount($value) { $this->Amount = $value; return $this; }             //decimal // TotalAmmount 
	
	/**
	 * TaxOverride for the document at line level. 
	 *
	 * @param string $value	 
	 */
	public function setTaxOverride($value) { $this->TaxOverride = $value; return $this; }	//decimal
	
    /**
	 * True if the document discount should be applied to this line 
	 *
	 * @param string $value	 
	 */
	public function setDiscounted( $value) { $this->Discounted = ($value ? true : false); return $this; }          //boolean  is discount applied to this item
    
	/**
	 * Sets the Address used as the "Ship From" location for a specific line item.
	 *
	 * @param string $value	 
	 */
	public function setOriginAddress(&$value) { $this->OriginAddress = $value; return $this; }			//Address
	
	/**
	 * Sets the Address used as the "Ship To" location for a specific line item. 
	 *
	 * @param string $value	 
	 */
	public function setDestinationAddress(&$value) { $this->DestinationAddress = $value; return $this; }	//Address
	
	/**
	 * True if tax is included in the line.
	 * @param boolean	 
	 */
	public function setTaxIncluded($value)
	{
		$this->TaxIncluded=$value;
	}
	
	/**
	 * True if tax is included in the line.
	 * @return boolean	 
	 */
	public function getTaxIncluded($value)
	{
		return $this->TaxIncluded;
	}
    
    public function registerAddressesIn(&$getTaxRequest)
    {
        if(is_null($this->OriginAddress))
        {
        	$this->OriginAddress = $getTaxRequest->getOriginAddress();
        	        	
        }
        if(is_null($this->DestinationAddress))
        {
        	$this->DestinationAddress = $getTaxRequest->getDestinationAddress();
        	
        }
    	$this->OriginCode = $getTaxRequest->registerAddress($this->OriginAddress);
        $this->DestinationCode = $getTaxRequest->registerAddress($this->DestinationAddress);
    }
	
    public function postFetchWithAddresses($addresses)
	{
		$this->OriginAddress = $addresses[$this->OriginCode];
		$this->DestinationAddress = $addresses[$this->DestinationCode];
	}

    //accessors
/**#@+
 * Accessor
 * @access public
 * @return string
 */
 
 
	public function getNo () { return $this->No; }							//string  // line Number of invoice
	public function getItemCode() { return $this->ItemCode; }				//string
	public function getDescription() { return $this->Description; }       //string
    public function getTaxCode() { return $this->TaxCode; }				//string
    public function getRevAcct() { return $this->RevAcct; }				//string
    public function getRef1() { return $this->Ref1; }						//string
    public function getRef2() { return $this->Ref2; }						//string
    public function getExemptionNo() { return $this->ExemptionNo; }       //string	//zero tax will result if filled in
    public function getCustomerUsageType() { return $this->CustomerUsageType; }   //string
    public function getBatchCode() { return $this->BatchCode; }			//string
	
	public function getQty() { return $this->Qty; }                 //decimal
    public function getAmount() { return $this->Amount; }              //decimal // TotalAmmount 
  	public function getTaxOverride() { return $this->TaxOverride; }	//decimal
	
	public function getDiscounted() { return $this->Discounted; }          //boolean  is discount applied to this item
	
	
	public function getOriginAddress() { return $this->OriginAddress; }			//Address
	public function getDestinationAddress() { return $this->DestinationAddress; }		//Address

	

/**#@-*/

}
	

?>