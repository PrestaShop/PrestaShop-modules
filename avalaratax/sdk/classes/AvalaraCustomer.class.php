<?php
/**
 * Customer.class.php
 */

/**
 * Contains customer data. Can be passed to AddCustomer using AddCustomerRequest. Also part of the InitiateExemptCertRequest request sent to the InitiateExemptCert exemption certificate service.
 * @see AddCustomerRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
*/
class AvalaraCustomer {
  private $CompanyCode; // string
  private $CustomerCode; // string
  private $NewCustomerCode; // string
  private $CustomerType; // string
  private $CustomerName; // string
  private $Attn; // string
  private $Address1; // string
  private $Address2; // string
  private $City; // string
  private $Region; // string
  private $PostalCode; // string
  private $Country; // string
  private $Phone; // string
  private $Fax; // string
  private $Email; // string
  private $ParentCustomerCode; // string

  public function setCompanyCode($value){$this->CompanyCode=$value;} // string
  public function getCompanyCode(){return $this->CompanyCode;} // string

  public function setCustomerCode($value){$this->CustomerCode=$value;} // string
  public function getCustomerCode(){return $this->CustomerCode;} // string

  public function setNewCustomerCode($value){$this->NewCustomerCode=$value;} // string
  public function getNewCustomerCode(){return $this->NewCustomerCode;} // string

  public function setCustomerType($value){$this->CustomerType=$value;} // string
  public function getCustomerType(){return $this->CustomerType;} // string

  public function setCustomerName($value){$this->CustomerName=$value;} // string
  public function getCustomerName(){return $this->CustomerName;} // string

  public function setAttn($value){$this->Attn=$value;} // string
  public function getAttn(){return $this->Attn;} // string

  public function setAddress1($value){$this->Address1=$value;} // string
  public function getAddress1(){return $this->Address1;} // string

  public function setAddress2($value){$this->Address2=$value;} // string
  public function getAddress2(){return $this->Address2;} // string

  public function setCity($value){$this->City=$value;} // string
  public function getCity(){return $this->City;} // string

  public function setRegion($value){$this->Region=$value;} // string
  public function getRegion(){return $this->Region;} // string

  public function setPostalCode($value){$this->PostalCode=$value;} // string
  public function getPostalCode(){return $this->PostalCode;} // string

  public function setCountry($value){$this->Country=$value;} // string
  public function getCountry(){return $this->Country;} // string

  public function setPhone($value){$this->Phone=$value;} // string
  public function getPhone(){return $this->Phone;} // string

  public function setFax($value){$this->Fax=$value;} // string
  public function getFax(){return $this->Fax;} // string

  public function setEmail($value){$this->Email=$value;} // string
  public function getEmail(){return $this->Email;} // string

  public function setParentCustomerCode($value){$this->ParentCustomerCode=$value;} // string
  public function getParentCustomerCode(){return $this->ParentCustomerCode;} // string

}

?>
