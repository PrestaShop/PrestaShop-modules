<?php
/**
 * PostTaxRequest.class.php
 */

/**
 * Data to pass to {@link TaxServiceSoap#commitTax}.
 * <p>
 * The request must specify all of CompanyCode, DocCode, and DocType in order to uniquely identify the document. 
 * </p>
 *
 * @see PostTaxResult
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
 
class PostTaxRequest extends TaxRequest
{
	private $DocDate;  //date
	private $TotalAmount;  // decimal
	private $TotalTax;  // decimal
	private $Commit=false; // boolean	
	private $NewDocCode;  //string
	
	
	public function __construct()
	{
		parent::__construct();		
		
		
	}
				
	public function getDocDate() { return $this->DocDate; }
	public function getTotalAmount() { return $this->TotalAmount; }
	public function getTotalTax() { return $this->TotalTax; }
	public function getCommit() { return $this->Commit; }
	public function getNewDocCode() { return $this->NewDocCode; }
		 	
	
	/**
	 * DocDate should be in the format yyyy-mm-dd 
	 *
	 * @param date $value	 
	 */
	public function setDocDate($value) { $this->DocDate = $value; return $this; }
	
	/**
	 *The total amount (not including tax) for the document. 
	 *
	 * @param decimal $value	 
	 */
	public function setTotalAmount($value) { $this->TotalAmount = $value; return $this; }
	
	/**
	 * The total tax for the document. 
	 *
	 * @param decimal $value	 
	 */
	public function setTotalTax($value) { $this->TotalTax = $value; return $this; }
	
	/**
	 * If this is set to True, AvaTax will Post and Commit the document in one call. 
	 * A very useful feature if you want to Post/Commit the document in one call this avoides one round trip to AvaTax server. 
	 *
	 * @param string $value	 
	 */
	public function setCommit($value) { $this->Commit = ($value ? true : false); return $this; }
	
	/**
	 * New Document Code for the document. 
	 * As on this version of SDK DocCode can be changed during post using NewDocCode. 
	  
	 * @param string $value	 
	 */
	public function setNewDocCode($value) { $this->NewDocCode = $value; }
		
	}
	
	


?>