<?php
/**
 * Customer.class.php
 */

/**
 * Contains customer data. Can be passed to {@link CustomerSave} using {@link CustomerSaveRequest}. 
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class Customer {
  private $CustomerCode; // string
  private $NewCustomerCode; // string
  private $ParentCustomerCode; // string
  private $Type; // string
  private $BusinessName; // string
  private $Attn; // string
  private $Address1; // string
  private $Address2; // string
  private $City; // string
  private $State; // string
  private $Country; // string
  private $Zip; // string
  private $Phone; // string
  private $Fax; // string
  private $Email; // string

  public function setCustomerCode($value){$this->CustomerCode=$value;} // string

/**
 * Customer identification code from client system. 
 */
  public function getCustomerCode(){return $this->CustomerCode;} // string

  public function setNewCustomerCode($value){$this->NewCustomerCode=$value;} // string

/**
 * NewCustomerCode is used to update the CustomerCode. 
 */
  public function getNewCustomerCode(){return $this->NewCustomerCode;} // string

  public function setParentCustomerCode($value){$this->ParentCustomerCode=$value;} // string

/**
 * Unique identifier for the Parent Customer record. 
 */
  public function getParentCustomerCode(){return $this->ParentCustomerCode;} // string

  public function setType($value){$this->Type=$value;} // string

/**
 * Customer type code 
 */
  public function getType(){return $this->Type;} // string

  public function setBusinessName($value){$this->BusinessName=$value;} // string

/**
 * Business or organization name 
 */
  public function getBusinessName(){return $this->BusinessName;} // string

  public function setAttn($value){$this->Attn=$value;} // string

/**
 * Name of the person to use in correspondence for the Customer record. 
 */
  public function getAttn(){return $this->Attn;} // string

  public function setAddress1($value){$this->Address1=$value;} // string

/**
 * Address1 of the Customer 
 */
  public function getAddress1(){return $this->Address1;} // string

  public function setAddress2($value){$this->Address2=$value;} // string

/**
 * Address2 of the Customer 
 */
  public function getAddress2(){return $this->Address2;} // string

  public function setCity($value){$this->City=$value;} // string

/**
 * City of the Customer 
 */
  public function getCity(){return $this->City;} // string

  public function setState($value){$this->State=$value;} // string

/**
 * State or province of the Customer 
 */
  public function getState(){return $this->State;} // string

  public function setCountry($value){$this->Country=$value;} // string

/**
 * ISO 2-character country code 
 */
  public function getCountry(){return $this->Country;} // string

  public function setZip($value){$this->Zip=$value;} // string

/**
 * ZIP or PostalCode of the Customer 
 */
  public function getZip(){return $this->Zip;} // string

  public function setPhone($value){$this->Phone=$value;} // string

/**
 * Phone number 
 */
  public function getPhone(){return $this->Phone;} // string

  public function setFax($value){$this->Fax=$value;} // string

/**
 * Fax number 
 */
  public function getFax(){return $this->Fax;} // string

  public function setEmail($value){$this->Email=$value;} // string

/**
 * Email address 
 */
  public function getEmail(){return $this->Email;} // string

}

?>
