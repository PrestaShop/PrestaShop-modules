<?php
/**
 * TaxServiceSoap.class.php
 */

/**
 * Proxy interface for the Avalara Tax Web Service.  It contains methods that perform remote calls
 * to the Avalara Tax Service. 
 *
 * TaxServiceSoap reads its configuration values from static variables defined
 * in ATConfig.class.php. This file must be properly configured with your security credentials.
 *
 * <p>
 * <b>Example:</b>
 * <pre>
 *  $taxService = new TaxServiceSoap();
 *  $result = $taxService->ping();
 * </pre>
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */


class TaxServiceSoap extends AvalaraSoapClient
{
    static $servicePath = '/Tax/TaxSvc.asmx';
    static protected $classmap = array(
        'BaseAddress' => 'Address',
        'ValidAddress' => 'ValidAddress',
        'Message' => 'AvalaraMessage',
        'ValidateRequest' => 'ValidateRequest',
        'IsAuthorizedResult' => 'IsAuthorizedResult',
        'PingResult' => 'PingResult',
        'ValidateResult' => 'ValidateResult',
		'Line'=>'Line',
		'AdjustTaxRequest'=>'AdjustTaxRequest',
		'AdjustTaxResult'=>'AdjustTaxResult',
		'CancelTaxRequest'=>'CancelTaxRequest',
		'CancelTaxResult'=>'CancelTaxResult',
		'CommitTaxRequest'=>'CommitTaxRequest',
		'CommitTaxResult'=>'CommitTaxResult',
		'GetTaxRequest'=>'GetTaxRequest',
		'GetTaxResult'=>'GetTaxResult',
		'GetTaxHistoryRequest'=>'GetTaxHistoryRequest',
		'GetTaxHistoryResult'=>'GetTaxHistoryResult',
		'PostTaxRequest'=>'PostTaxRequest',
		'PostTaxResult'=>'PostTaxResult',
		'ReconcileTaxHistoryRequest'=>'ReconcileTaxHistoryRequest',
		'ReconcileTaxHistoryResult'=>'ReconcileTaxHistoryResult',
		'TaxLine'=>'TaxLine',
        'TaxDetail' => 'TaxDetail',
		'ApplyPaymentRequest'=>'ApplyPaymentRequest',
		'ApplyPaymentResult'=>'ApplyPaymentResult',
		'BaseResult'=>'BaseResult',
		'TaxOverride'=>'TaxOverride'
		);
        
public function __construct($configurationName = 'Default')
    {
        $config = new ATConfig($configurationName);
        
        $this->client = new DynamicSoapClient   (
            $config->taxWSDL,
            array
            (
                'location' => $config->url.$config->taxService, 
                'trace' => $config->trace,
                'classmap' => TaxServiceSoap::$classmap
            ), 
            $config
        );
    }



    /**
     * Calculates taxes on a document such as a sales order, sales invoice, purchase order, purchase invoice, or credit memo.
     * <br>The tax data is saved Sales Invoice and Purchase Invoice document types {@link GetTaxRequest#getDocType}.
     *
     * @param getTaxRequest  -- Tax calculation request
     *
     * @return GetTaxResult
     * @throws SoapFault
     */
    public function getTax(&$getTaxRequest)
    {
		$getTaxRequest->prepare();
		return $this->client->GetTax(array('GetTaxRequest' => $getTaxRequest))->GetTaxResult;
    }

    /**
     * Retrieves a previously calculated tax document.
     * <p>
     * This is only available for saved tax documents (Sales Invoices, Purchase Invoices).
     * </p>
     * <p>
     * A document can be indicated solely by the {@link PostTaxRequest#getDocId} if it is known.
     * Otherwise the request must specify all of {@link PostTaxRequest#getCompanyCode}, see {@link PostTaxRequest#getDocCode}
     * and {@link PostTaxRequest#getDocType} in order to uniquely identify the document.
     * </p>
     *  
     * @param getTaxHistoryRequest a {@link GetTaxHistoryRequest} object indicating the document for which history should be retrieved.
     * @return a {@link GetTaxHistoryResult} object
     * @throws SoapFault
     */
    /*public com.avalara.avatax.services.tax.GetTaxHistoryResult getTaxHistory(com.avalara.avatax.services.tax.GetTaxHistoryRequest getTaxHistoryRequest) throws SoapFault;
	*/
	public function getTaxHistory(&$getTaxHistoryRequest)
    {
		$result = $this->client->GetTaxHistory(array('GetTaxHistoryRequest'=>$getTaxHistoryRequest))->GetTaxHistoryResult;
		$result->getGetTaxRequest()->postFetch();
		return $result;
    }

    /**
     * Posts a previously calculated tax
     * <p>
     * This is only available for saved tax documents (Sales Invoices, Purchase Invoices).
     * </p>
     * <p>
     * A document can be indicated solely by the {@link PostTaxRequest#getDocId} if it is known.
     * Otherwise the request must specify all of {@link PostTaxRequest#getCompanyCode}, {@link PostTaxRequest#getDocCode}, and
     * {@link PostTaxRequest#getDocType} in order to uniquely identify the document.
     * </p>
     *
     * @param postTaxRequest a {@link PostTaxRequest} object indicating the document that should be posted.
     * @return a {@link PostTaxResult} object
     * @throws SoapFault
     */
	 
    /*public com.avalara.avatax.services.tax.PostTaxResult postTax(com.avalara.avatax.services.tax.PostTaxRequest postTaxRequest) throws SoapFault;
	*/
    public function postTax(&$postTaxRequest)
    {		
		return $this->client->PostTax(array('PostTaxRequest'=>$postTaxRequest))->PostTaxResult;
    }

    /**
     * Commits a previously posted tax.
     * <p>
     * This is only available for posted tax documents (Sales Invoices, Purchase Invoices). Committed documents cannot
     * be changed or deleted.
     * </p>
     * <p>
     * A document can be indicated solely by the {@link CommitTaxRequest#getDocId} if it is known. Otherwise the
     * request must specify all of {@link CommitTaxRequest#getCompanyCode}, {@link CommitTaxRequest#getDocCode}, and
     * {@link CommitTaxRequest#getDocType} in order to uniquely identify the document.
     * </p>
     *
     * @param commitTaxRequest a {@link CommitTaxRequest} object indicating the document that should be committed.
     * @return a {@link CommitTaxResult} object
     * @throws SoapFault
     */
	 
    /*public com.avalara.avatax.services.tax.CommitTaxResult commitTax(com.avalara.avatax.services.tax.CommitTaxRequest commitTaxRequest) throws SoapFault;
	*/
	public function commitTax(&$commitTaxRequest)
    {
		return $this->client->CommitTax(array('CommitTaxRequest'=>$commitTaxRequest))->CommitTaxResult;
    }
    /**
     * Cancels a previously calculated tax;  This is for use as a
     * compensating action when posting on the client fails to complete.
     * <p>
     * This is only available for saved tax document types (Sales Invoices, Purchase Invoices). A document that is saved
     * but not posted will be deleted if canceled. A document that has been posted will revert to a saved state if canceled
     * (in this case <b>CancelTax</b> should be called with a {@link CancelTaxRequest#getCancelCode} of
     * <i>PostFailed</i>). A document that has been committed cannot be reverted to a posted state or deleted. In the case
     * that a document on the client side no longer exists, a committed document can be virtually removed by calling
     * <b>CancelTax</b> with a <b>CancelCode</b> of <i>DocDeleted</i>. The record will be retained in history but removed
     * from all reports.
     * </p>
     * <p>
     * A document can be indicated solely by the {@link CancelTaxRequest#getDocId} if it is known. Otherwise the request
     * must specify all of {@link CancelTaxRequest#getCompanyCode}, {@link CancelTaxRequest#getDocCode}, and
     * {@link CancelTaxRequest#getDocType} in order to uniquely identify the document.
     *
     * @param cancelTaxRequest a {@link CancelTaxRequest} object indicating the document that should be canceled.
     * @return   a {@link CancelTaxResult} object
     * @throws SoapFault
     */
     /* public com.avalara.avatax.services.tax.CancelTaxResult cancelTax(com.avalara.avatax.services.tax.CancelTaxRequest cancelTaxRequest) throws SoapFault;
	 */
	public function cancelTax(&$cancelTaxRequest)
    {
		return $this->client->CancelTax(array('CancelTaxRequest'=>$cancelTaxRequest))->CancelTaxResult;
    }

    /**
     * Reconciles tax history to ensure the client data matches the
     * AvaTax history.
     * <p>The Reconcile operation allows reconciliation of the AvaTax history with the client accounting system.
     * It must be used periodically according to your service contract.
     * </p>
     * <p>
     * Because there may be a large number of documents to reconcile, it is designed to be called repetitively
     * until all documents have been reconciled.  It should be called until no more documents are returned.
     * Each subsequent call should pass the previous results {@link ReconcileTaxHistoryRequest#getLastDocId}.
     * </p>
     * <p>
     * When all results have been reconciled, Reconcile should be called once more with
     * {@link ReconcileTaxHistoryRequest#getLastDocId}
     * equal to the last document code processed and {@link ReconcileTaxHistoryRequest#isReconciled} set to true to indicate
     * that all items have been reconciled.  If desired, this may be done incrementally with each result set.  Just send
     * Reconciled as true when requesting the next result set and the prior results will be marked as reconciled.
     * </p>
     * <p>
     * The {@link #postTax}, {@link #commitTax}, and {@link #cancelTax} operations can be used to correct any differences.
     * {@link #getTax} should be called if any committed documents are out of balance
     * ({@link GetTaxResult#getTotalAmount} or {@link GetTaxResult#getTotalTax}
     * don't match the accounting system records).  This is to make sure the correct tax is reported.
     * </p>
     *
     * @param reconcileTaxHistoryRequest  a Reconciliation request
     * @return A collection of documents that have been posted or committed since the last reconciliation.
     * @throws SoapFault
     */
    /*public com.avalara.avatax.services.tax.ReconcileTaxHistoryResult reconcileTaxHistory(com.avalara.avatax.services.tax.ReconcileTaxHistoryRequest reconcileTaxHistoryRequest) throws SoapFault;
*/
	public function reconcileTaxHistory(&$reconcileTaxHistoryRequest)
    {
		return $this->client->ReconcileTaxHistory(array('ReconcileTaxHistoryRequest'=>$reconcileTaxHistoryRequest))->ReconcileTaxHistoryResult;
    }

/**
     * Adjusts a previously calculated tax.
     * <p>
     * This is only available for unlocked tax documents (Sales Invoices, Purchase Invoices).      * </p>
     * <p>
      * </p>
     *
     * @param adjustTaxRequest a {@link AdjustTaxRequest} object indicating the document that should be edited.
     * @return a {@link AdjustTaxResult} object
     * @throws SoapFault
     */
	 
    /*public com.avalara.avatax.services.tax.CommitTaxResult commitTax(com.avalara.avatax.services.tax.CommitTaxRequest commitTaxRequest) throws SoapFault;
	*/
	public function adjustTax(&$adjustTaxRequest)
    {
		$adjustTaxRequest->getGetTaxRequest()->prepare();
		return $this->client->AdjustTax(array('AdjustTaxRequest'=>$adjustTaxRequest))->AdjustTaxResult;
    }
    /**
     * Checks authentication of and authorization to one or more
     * operations on the service.
     * 
     * This operation allows pre-authorization checking of any
     * or all operations. It will return a comma delimited set of
     * operation names which will be all or a subset of the requested
     * operation names.  For security, it will never return operation
     * names other than those requested (no phishing allowed).
     * 
     * <b>Example:</b><br>
     * <code> isAuthorized("GetTax,PostTax")</code>
     *
     * @param string $operations  a comma-delimited list of operation names
     *
     * @return IsAuthorizedResult
     * @throws SoapFault
     */
	 

    public function isAuthorized($operations)
    {
        return $this->client->IsAuthorized(array('Operations' => $operations))->IsAuthorizedResult;
    }
    
    /**
     * Verifies connectivity to the web service and returns version
     * information about the service.
     *
     * <b>NOTE:</b>This replaces TestConnection and is available on
     * every service.
     *
     * @param string $message for future use
     * @return PingResult
     * @throws SoapFault
     */

    public function ping($message = '')
    {
        return $this->client->Ping(array('Message' => $message))->PingResult;
    }
    
    /**
     * This method is used to apply a payment to a document for cash basis accounting. Applies a payment date to an existing invoice 
	 * It sets the document PaymentDate and changes the reporting date from the DocDate default. It may be called before or after a document is committed. It should not be used for accrual basis accounting       
     *
     * @param ApplyPaymentRequest $applyPaymentRequest
     * @return ApplyPaymentResult
     */
    
    public function applyPayment(&$applyPaymentRequest)
    {		
		return $this->client->ApplyPayment(array('ApplyPaymentRequest' => $applyPaymentRequest))->ApplyPaymentResult;
    }    		    

}

?>
