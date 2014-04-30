<?php
/**
 * AdjustTaxRequest.class.php
 */

/**
 * Data to pass to {@link TaxServiceSoap#adjustTax}.
 *
 * @see AdjustTaxRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
 
 
  
class AdjustTaxRequest 
{
	private $AdjustmentReason;			//int
	private $AdjustmentDescription;		//string
	private $GetTaxRequest;				//string

		
    /**     
     * Adjustment Description is required when AdjustmentReason is "Other" for enhanced tracability. 
     *
     * @param string $value
     */
	public function setAdjustmentDescription($value) { $this->AdjustmentDescription = $value; }
  


	/**
	 * Reason for Adjusting document.
	 * <pre>
     * Sets a valid reason for the given AdjustTax call. Adjustment Reason is a high level classification of why an Original Document is being modified.
     * 0 Not Adjusted 
	 * 1 Sourcing Issue 
	 * 2 Reconciled with General Ledger 
	 * 3 Exemption Certificate Applied 
	 * 4 Price or Quantity Adjusted 
	 * 5 Item Returned 
	 * 6 Item Exchanged 
	 * 7 Bad Debt 
	 * 8 Other (Explain) 
		Must provide AdjustmentDescription
 		<pre>
     * 
     * Please visit Avalara's Administrative Console's transaction adjustment section for latest AdjustmentReasonList. 
	 *
	 * @param int $value	 
	 */		
    public function setAdjustmentReason($value) { $this->AdjustmentReason = $value;}
    
    
	/**
	 * Holds the data for Adjust Tax call. It takes the information needed for GetTax call. 
	 *
	 * @param GetTaxRequest $value
	 */
    public function setGetTaxRequest($value) { $this->GetTaxRequest = $value;}
	 
 	public function getAdjustmentReason() { return $this->AdjustmentReason;}	//int

    public function getAdjustmentDescription() { return $this->AdjustmentDescription;}	
        

    public function getGetTaxRequest() { return $this->GetTaxRequest;}	//string   invoice number
 

	

	
}

?>