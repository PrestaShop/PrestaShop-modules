<?php
/**
 * TaxOverride.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

class TaxOverride
{
    private $TaxOverrideType;   //TaxOverrideType
    private $TaxAmount;         //decimal
    private $TaxDate;           //date
    private $Reason;            //string
    
    
    
    public function __construct()
    {
    	$this->TaxAmount=0.0;
    	
    	$dateTime=new DateTime();
        $dateTime->setDate(1900,01,01);
        
        $this->TaxDate=$dateTime->format("Y-m-d");
    }
    
    public function setTaxOverrideType($value){ $this->TaxOverrideType=$value; }   //TaxOverrideType
    public function setTaxAmount($value){$this->TaxAmount=$value;}         //decimal
    public function setTaxDate($value){$this->TaxDate=$value;}           //date
    public function setReason($value){$this->Reason=$value;}            //string
    
    
    public function getTaxOverrideType(){ return $this->TaxOverrideType; }   //TaxOverrideType
    public function getTaxAmount(){return $this->TaxAmount;}         //decimal
    public function getTaxDate(){return $this->TaxDate;}           //date
    public function getReason(){return $this->Reason;}            //string
    
    
}

?>