<?php
/**
 * ReconcileTaxHistoryRequest.class.php
 */

/**
 * Data to pass to {@link TaxServiceSoap#reconcileTaxHistory(ReconcileTaxHistoryRequest)}
 * @see ReconcileTaxHistoryResult
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class ReconcileTaxHistoryRequest// extends TaxRequest
{
	private $CompanyCode;   //string
    private $StartDate;   //date
    private $EndDate;   //date
    private $DocStatus;   //int
    
    private $DocType; // DocType
  	private $LastDocCode; // string
  	private $PageSize; // int
  	
  	private $Reconciled=true;
	
    public function __construct()
    {	
		//parent::__construct();
		$this->EndDate = date("Y-m-d");
		$this->DocStatus =DocStatus::$Any;
		
    }
	

	
	public function getCompanyCode() { return $this->CompanyCode;}
	
	
	public function getstartDate() { return $this->StartDate; }
	public function getEndDate() { return $this->EndDate; }
	public function getDocStatus() { return $this->DocStatus; }
	
	/**
	 * Sets the client application company reference code. 
	 *
	 * @param string $value	 
	 */
	public function setCompanyCode($value) {  $this->CompanyCode = $value; return $this; }
	
		
	
	/**
	 * Set this to retrieve data FROM a specific date. 
	 *
	 * @param date $value	 
	 */
	public function setStartDate($value) {  $this->StartDate = $value; return $this; }
	
	/**
	 * Set this to retrieve data TO a specific date. 
	 *
	 * @param date $value	 
	 */
	public function setEndDate($value) {  $this->EndDate = $value; return $this; }
	
	/**
	 * Set this to retrieve data with a specific DocStatus. 
	 *
	 * @param string $value	 
	 */
	public function setDocStatus($value) { DocStatus::Validate($value); $this->DocStatus = $value; return $this; }
	
	
	public function setDocType($value){$this->DocType=$value;} // DocType
  	public function getDocType(){return $this->DocType;} // DocType

  	public function setLastDocCode($value){$this->LastDocCode=$value;} // string
  	public function getLastDocCode(){return $this->LastDocCode;} // string

  	public function setPageSize($value){$this->PageSize=$value;} // int
  	public function getPageSize(){return $this->PageSize;} // int


}

?>