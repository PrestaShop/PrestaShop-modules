<?php
/**
 * AddCustomerRequest.class.php
 */

/**
 * Data to pass to {@link AvaCertServiceSoap#AddCustomer}.
 *
 * @see AddCustomerResult
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */
class AddCustomerRequest {
  private $Customer; // Customer

  /**
   * Sets the customer value for this AddCustomerRequest.
   * 
   * @param Customer $value    
   */
  public function setCustomer($value){$this->Customer=$value;} // Customer
  
  /**
   * Gets the customer value for this AddCustomerRequest.
   * @return Customer
   */
  public function getCustomer(){return $this->Customer;} // Customer

}

?>
