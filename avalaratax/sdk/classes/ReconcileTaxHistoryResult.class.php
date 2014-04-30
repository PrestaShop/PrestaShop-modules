<?php
/**
 * ReconcileTaxHistoryResult.class.php
 */

/**
 * Result data returned from {@link TaxServiceSoap#reconcileTaxHistory}.
 * @see ReconcileTaxHistoryRequest
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class ReconcileTaxHistoryResult //extends SearchTaxHistoryResult
{
// SearchTaxHistoryResult innards - work around a bug in SoapClient
	
    public $GetTaxResults;     // array of GetTaxResult
    private $RecordCount; // int
    private $LastDocCode; //string
    


    /**
     * Gets zero or more {@link GetTaxResult} summaries matching search criteria.
     * <p>
     * If <b>LastDocId</b> was not specified by the {@link ReconcileTaxHistoryRequest},
     * then this is the first set of records that need reconciliation. If <b>LastDocId</b> was specified,
     * the collection represents the next set of records after <b>LastDocId</b>. If the collection is
     * empty, then all records have been reconciled and the result's <b>LastDocId</b> will be set to the
     * last record of the last result set.
     * <br>
     * The GetTaxResults are returned in an Axis wrapper {@link ArrayOfGetTaxResult}, which has a
     * raw GetTaxResult[] array accessible via its {@link ArrayOfGetTaxResult#getGetTaxResult} method.
     * <pre>
     * <b>Example:</b>
     * $result = $taxSvc->reconcileTaxHistory($request);
     * foreach($result->getTaxResults() as $taxResult)
     * {
     *      ...
     * }
     *
     * </pre>
     * @see GetTaxResult
     * @return array
     */

    public function getGetTaxResults()
     {

     	if(isset($this->GetTaxResults->GetTaxResult))
     	{
     		return EnsureIsArray($this->GetTaxResults->GetTaxResult);
     	}
     	else
     	{
     		return null; 
     	}
     }
    
    public function setRecordCount($value){$this->RecordCount=$value;} // int
  	public function getRecordCount(){return $this->RecordCount;} // int
  	
  	public function setLastDocCode($value)
  	{
  		$this->LastDocCode=$value;
  	}
  	public function getLastDocCode()
  	{
  		return $this->LastDocCode;
  	}

    
    
// BaseResult innards - work around a bug in SoapClient

/**
 * @var string
 */
    private $TransactionId;
/**
 * @var string must be one of the values defined in {@link SeverityLevel}.
 */
    private $ResultCode = 'Success';
/**
 * @var array of Message.
 */
    private $Messages = array();

/**
 * Accessor
 * @return string
 */
    public function getTransactionId() { return $this->TransactionId; }
/**
 * Accessor
 * @return string
 */
    public function getResultCode() { return $this->ResultCode; }
/**
 * Accessor
 * @return array
 */
    public function getMessages() { return EnsureIsArray($this->Messages->Message); }


	
}

?>