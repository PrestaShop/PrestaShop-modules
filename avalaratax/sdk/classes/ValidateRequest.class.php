<?php
/**
 * ValidateRequest.class.php
 */

/**
 * Data wrapper used internally to pass arguments within {@link AddressServiceSoap#validate}. End users should not need to use this class.
 * 
 * <pre>
 * <b>Example:</b>
 * $svc = new AddressServiceSoap();
 *
 * $address = new Address();
 * $address->setLine1("900 Winslow Way");
 * $address->setCity("Bainbridge Island");
 * $address->setRegion("WA");
 * $address->setPostalCode("98110");
 *
 * ValidateRequest validateRequest = new ValidateRequest();
 * validateRequest.setAddress(address);
 * validateRequest.setTextCase(TextCase.Upper);
 *
 * ValidateResult result = svc.validate(validateRequest);
 * ArrayOfValidAddress arrValids = result.getValidAddresses();
 * int numAddresses = (arrValids == null ||
 *         arrValids.getValidAddress() == null ? 0 :
 *         arrValids.getValidAddress().length);
 * System.out.println("Number of Addresses is " + numAddresses);
 * </pre>
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */
 
 //public function validate($address, $textCase = 'Default', $coordinates = false)
    //{
       // $request = new ValidateRequest($address, ($textCase ? $textCase : TextCase::$Default), $coordinates);
       // return $this->client->Validate(array('ValidateRequest' => $request))->ValidateResult;
    //}


class ValidateRequest
{
    private $Address;
    private $TextCase = 'Default';
    private $Coordinates = false;
    private $Taxability=false;
    
    public function __construct($address = null, $textCase = 'Default', $coordinates = false)
    {
        $this->setAddress($address);
        $this->setTextCase($textCase);
        $this->setCoordinates($coordinates);
    }
    
    // mutators
    /**
     * The address to Validate.
     * <pre>
     * <b>Example:</b>
     * $address = new Address();
     * $address->setLine1("900 Winslow Way");
     * $address->setCity("Bainbridge Island");
     * $address->setRegion("WA");
     * $address->setPostalCode("98110");
     *
     * $validateRequest = new ValidateRequest();
     * $validateRequest->setAddress(address);
     * $validateRequest->setTextCase(TextCase::$Upper);
     *
     * $result = svc->validate(validateRequest);
     * </pre>
     *
     * @var Address
     */
    
    public function setAddress(&$value) { $this->Address = $value; return $this; }
    
    /**
     * The casing to apply to the validated address(es).
     * <pre>
     * <b>Example:</b>
     * <b>Example:</b>
     * $address = new Address();
     * $address->setLine1("900 Winslow Way");
     * $address->setCity("Bainbridge Island");
     * $address->setRegion("WA");
     * $address->setPostalCode("98110");
     *
     * $validateRequest = new ValidateRequest();
     * $validateRequest->setAddress(address);
     * $validateRequest->setTextCase(TextCase::$Upper);
     *
     * $result = svc->validate(validateRequest);
     * </pre>
     *
     * @var string
     * @see TextCase
     */
    
    public function setTextCase($value) 
	{ 
		if($value) 
		{ 
			TextCase::Validate($value); 
			$this->TextCase = $value; 
		} 
		else 
		{ 
			$this->TextCase = TextCase::$Default; 
		} 
		return $this; 
	}

    /**
     * Sets whether to fetch the coordinates value for this ValidateRequest.
     *  <p>
     *  True will return the @see ValidAddress#Latitude and @see ValidAddress#Longitude values for the @see ValidAddresses
     *  Default value is <b>false</b>
     *  </p>
     * @var boolean
     */
    public function setCoordinates($value) { $this->Coordinates = ($value ? true : false); return $this; }
    
    
    //@author:swetal
    public function setTaxability($value)
    {
        $this->Taxability=$value;
    }
    // accessors
    /**
     * The address to Validate.
     * <pre>
     * <b>Example:</b>
     * $address = new Address();
     * $address->setLine1("900 Winslow Way");
     * $address->setCity("Bainbridge Island");
     * $address->setRegion("WA");
     * $address->setPostalCode("98110");
     *
     * $validateRequest = new ValidateRequest();
     * $validateRequest->setAddress(address);
     * $validateRequest->setTextCase(TextCase::$Upper);
     *
     * $result = svc->validate(validateRequest);
     * </pre>
     *
     * @return Address
     */
    
    public function getAddress() { return $this->Address; }
    
    /**
     * The casing to apply to the validated address(es).
     * <pre>
     * <b>Example:</b>
     * <b>Example:</b>
     * $address = new Address();
     * $address->setLine1("900 Winslow Way");
     * $address->setCity("Bainbridge Island");
     * $address->setRegion("WA");
     * $address->setPostalCode("98110");
     *
     * $validateRequest = new ValidateRequest();
     * $validateRequest->setAddress(address);
     * $validateRequest->setTextCase(TextCase::$Upper);
     *
     * $result = svc->validate(validateRequest);
     * </pre>
     *
     * @return string
     * @see TextCase
     */
    
    public function getTextCase() { return $this->TextCase; }
    
    /**
     * Returns whether to return the coordinates value for this ValidateRequest.
     *  <p>
     *  True will return the @see ValidAddress#Latitude and @see ValidAddress#Longitude values for the @see ValidAddresses
     *  Default value is <b>false</b>
     *  </p>
     * @return boolean
     */

    public function getCoordinates() { return $this->Coordinates; }
}

?>
