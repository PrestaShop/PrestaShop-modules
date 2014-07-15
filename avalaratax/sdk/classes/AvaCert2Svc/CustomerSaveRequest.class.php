<?php
/**
 * CustomerSaveRequest.class.php
 */

/**
 * Input for {@link CustomerSave}.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class CustomerSaveRequest {
  private $CompanyCode; // string
  private $Customer; // Customer

  public function setCompanyCode($value){$this->CompanyCode=$value;} // string

/**
 * Company Code of the company to which the customer belongs. 
 */
  public function getCompanyCode(){return $this->CompanyCode;} // string

  public function setCustomer($value){$this->Customer=$value;} // Customer

/**
 * The customer to add. 
 */
  public function getCustomer(){return $this->Customer;} // Customer

}

?>
