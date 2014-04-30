<?php
/**
 * GetTaxHistoryRequest.class.php
 */

/**
 * Data to pass to {@link TaxServiceSoap#getTaxHistory}.
 * <p>
 * The request must specify all of CompanyCode, DocCode, and DocType in order to uniquely identify the document. 
 * </p>
 *
 * @see GetTaxHistoryResult
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class GetTaxHistoryRequest extends TaxRequest 
{
    private $DetailLevel;   
    
    public function __construct()
    {
        parent::__construct();
		$this->DetailLevel = DetailLevel::$Document;  // this is right Document
		$this->DocType = DocumentType::$SalesOrder;  // this is right Document

    }
	
    /**
     * Specifies the level of detail to return.
     * 
     * @return detailLevel
     * @var string
     * @see DetailLevel
     */

    public function getDetailLevel() { return $this->DetailLevel; }

   /**
     * Specifies the level of detail to return.
     * 
     * @see DetailLevel
     * @return string
     */

    	public function setDetailLevel($value) { DetailLevel::Validate($value); $this->DetailLevel = $value; return $this; }			//Summary or Document or Line or Tax or Diagnostic - enum

}

?>