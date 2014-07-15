<?php
/**
 * SearchTaxHistoryResult.class.php
 */

/**
 * Result data returned from {@link TaxSvcSoap#reconcileTaxHistory}.
 * This class encapsulates the data and methods used by {@link ReconcileTaxHistoryResult}.
 *
 * @see ReconcileTaxHistoryRequest
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class SearchTaxHistoryResult //extends BaseResult
{

    private $GetTaxResults;     // array of GetTaxResult
    private $LastDocId;         // string


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

    public function getTaxResults() { return EnsureIsArray($this->GetTaxResults->GetTaxResult); }
    
    /**
     * Indicates the last Document Code ({@link GetTaxResult#getDocId}) the results list.
     * <p>
     * If {@link #getGetTaxResults} is not empty, then this
     * <b>LastDocId</b> should be passed to the next {@link ReconcileTaxHistoryRequest}.
     * If {@link #getGetTaxResults} is empty, then this <b>LastDocId</b> can be
     * passed to {@link ReconcileTaxHistoryRequest} with the request's
     * {@link ReconcileTaxHistoryRequest#isReconciled} flag
     * set to true in order to reconcile all documents up to and including the LastDocId.
     * </p>
     *
     * @see ReconcileTaxHistoryResult
     * @return string
     */

    public function getLastDocId() { return $this->LastDocId; }
    
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