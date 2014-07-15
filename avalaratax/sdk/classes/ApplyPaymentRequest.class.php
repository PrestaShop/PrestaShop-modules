<?php
/**
 * ApplyPaymentRequest.class.php
 */

/**
 * ApplyPaymentRequest.class.php
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class ApplyPaymentRequest
{    
    private $CompanyCode;   //string
    private $DocType;       //DocumentType
    private $DocCode;       //string
    private $PaymentDate;   //date
    

	public function __construct()
    {
    	$this->DocType=DocumentType::$SalesOrder;
    }
        
    /**
     * Sets the companyCode value for this ApplyPaymentRequest.
     *
     * @param string $value
     */
    public function setCompanyCode($value){ $this->CompanyCode=$value;}   //string
    
    /**
     * Sets the docCode value for this ApplyPaymentRequest.
     *
     * @param DocumentType $value
     */
    public function setDocType($value){ $this->DocType=$value;}       //DocumentType
    
    /**
     * Sets the docType value for this ApplyPaymentRequest.
     *
     * @param string $value
     */
    public function setDocCode($value){ $this->DocCode=$value;}       //string
    
    /**
     * PaymentDate should be in the format yyyy-mm-dd
     *
     * @param date $value
     */
    public function setPaymentDate($value){ $this->PaymentDate=$value;}   //date
    
        
    /**
     * Gets the companyCode value for this ApplyPaymentRequest.
     *
     * @return string
     */
    public function getCompanyCode(){ return $this->CompanyCode;}   //string
    
    /**
     * Gets the docType value for this ApplyPaymentRequest.
     *
     * @return DocumentType
     */
    public function getDocType(){ return $this->DocType;}       //DocumentType
    
    /**
     *  Gets the docCode value for this ApplyPaymentRequest.
     *
     * @return unknown
     */
    public function getDocCode(){ return $this->DocCode;}       //string
    /**
	 * PaymentDate should be in the format yyyy-mm-dd 
	 *
	 * @param date $value	 
	 */
    public function getPaymentDate(){ return $this->PaymentDate;}   //date
    
}

?>