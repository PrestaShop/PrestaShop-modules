<?php
/**
 * TaxLine.class.php
 */
 
 /**
 * Contains Tax data; Retunded form {@link AddressServiceSoap#getTax};
 * Also part of the {@link GetTaxRequest}
 * result returned from the {@link TaxServiceSoap#getTax} tax calculation service;
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class TaxLine
{

	private $No; //string 
	private $TaxCode; //string 
	private $Taxability; //boolean 
	private $BoundaryLevel; //BoundaryLevel 
	private $Exemption; //decimal 
	private $Discount; //decimal 
	private $Taxable; //decimal 
	private $Rate; //decimal 
	private $Tax; //decimal 
	private $ExemptCertId; //int 
	private $TaxDetails; //ArrayOfTaxDetail
	
	//@author:swetal
	//added following properties to upgrade to 5.3 interface
	private $TaxCalculated;	//decimal
	private $ReportingDate;	//date
	private $AccountingMethod;//String
	
	private $TaxIncluded;		//boolean
	
/**
 * Accessor
 * @return string
 */
    public function getNo() { return $this->No; }	
	
	
	/**
 * Accessor
 * @return string
 */
    public function getTaxCode() { return $this->TaxCode; }	
	/**
 * Accessor
 * @return boolean
 */
    public function getTaxability() { return $this->Taxability; }	
	/**
 * Accessor
 * @see BoundaryLevel
 * @return string
 */
    public function getBoundaryLevel() { return $this->BoundaryLevel; }	
	/**
 * Accessor
 * @return decimal
 */
    public function getExemption() { return $this->Exemption; }	
	/**
 * Accessor
 * @return decimal
 */
    public function getDiscount() { return $this->Discount; }	
	/**
 * Accessor
 * @return decimal
 */
    public function getTaxable() { return $this->Taxable; }	
/**
 * Accessor
 * @return decimal
 */
    public function getRate() { return $this->Rate; }	
	/**
 * Accessor
 * @return string
 */
    public function getTax() { return $this->Tax; }
	

	/**
 * Accessor
 * @return decimal
 */
    public function getTaxDetails() { return EnsureIsArray($this->TaxDetails->TaxDetail); }	
	
		
	/**
 * Accessor
 * @return int
 */
    public function getExemptCertId() { return $this->ExemptCertId; }
    
    /**
 * Accessor
 * @return decimal
 */
	public function getTaxCalculated(){ return $this->TaxCalculated; }	//decimalt
	public function getReportingDate(){ return $this->ReportingDate;}	//date
	public function getAccountingMethod(){ return $this->AccountingMethod;}//String
	
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
	public function getTaxIncluded()
	{
		return $this->TaxIncluded;
	}
    
    

}
?>