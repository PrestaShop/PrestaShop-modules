<?php
/**
 * TaxDetail.class.php
 */

/**
 * Holds calculated tax information by jurisdiction.
 *
 * @see ArrayOfTaxDetail
 * @see TaxLine
 * @see GetTaxResult
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class TaxDetail
{

	private $JurisType;     //JurisdictionType 
	private $JurisCode;     //string 
	private $TaxType;     //TaxType 
	
	private $Base;		//decimal // See Taxable
	private $Taxable;     //decimal 
	private $Rate;		//decimal 
	
	private $Tax;		//decimal 
	private $NonTaxable;     //decimal 
	private $Exemption;     //decimal 
	private $JurisName;     //string 
	private $TaxName;     //string 
	private $TaxAuthorityType;  // int
	
	//@author:swetal
	//Added new properties to upgrade it to 5.3 interface
	private $Country;	//string
	private $Region; 	//string
	private $TaxCalculated;	//decimal
	private $TaxGroup;	//string
	
	//Task# 25610
	private $StateAssignedNo;
	
	public function getStateAssignedNo()
	{
		return $this->StateAssignedNo;
	}
 
	
	
	/**
     * Gets the JurisdictionType.
     * <p>
     * 
     
	 * </p>
     * @see JurisdictionType
	   @see GetTaxResults
     * @return JurisdictionType
     */

	public function getJurisType() {return $this->JurisType; }
	
	/**
     * Gets the JurisCode.
     * <p>
         * </p>
     * @see JurisCode
	   @see GetTaxResults
     * @return string
     */

	public function getJurisCode() {return $this->JurisCode; }

   /**
     * Gets the TaxType.
     * <p>
         * </p>
	   @see GetTaxResults
     * @return TaxType
     */

	public function getTaxType() {return $this->TaxType; }

  /**
     * Gets the Taxable amount.
     * <p>
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getTaxable() {return $this->Taxable; }
	
  /**
     * Gets the Taxable amount.
     * <p>
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getBase() {return $this->Base; }


  /**
     * Gets the Rate amount.
     * <p>
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getRate() {return $this->Rate; }

	/**
     * Gets the Tax amount.
     * <p>
	 The tax amount, i.e. the calculated tax (base() * rate())
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getTax() {return $this->Tax; }
	
	 /**
     * Gets the non-taxable amount..
     * <p>
	The non-taxable amount.
         * </p>
	   @see GetTaxResults
     * @return decimal
     */
  
	public function getNonTaxable() {return $this->NonTaxable; }


	 /**
     * Gets theExemption amount..
     * <p>
	The exempt amount for this TaxDetail.
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getExemption() {return $this->Exemption; }
	/**
     * 	Gets the jurisdiction name for this TaxDetail.

     * <p>
	Gets the jurisdiction name for this TaxDetail.
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getJurisName() {return $this->JurisName; }
	/**
     * 
     * <p>
	Gets the taxName value.
	It further defines tax and jurisdiction.         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getTaxName() {return $this->TaxName; }
	/**
     * Gets the taxAuthorityType value for this TaxDetail.
     * <p>
	Gets the taxAuthorityType value for this TaxDetail.
         * </p>
	   @see GetTaxResults
     * @return decimal
     */

	public function getTaxAuthorityType() {return $this->TaxAuthorityType; }
	
	//@author:swetal
	public function getCountry(){ return $this->Country;}	
	public function getRegion(){ return $this->Region;} 	
	public function getTaxCalculated(){ return $this->TaxCalculated;}	
	public function getTaxGroup(){ return $this->TaxGroup;}	

	    
}

	
	
	

?>