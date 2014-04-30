<?php
/**
 * AvalaraAddress.class.php
 */
 
 /**
 * Contains address data; Can be passed to {@link AddressServiceSoap#validate};
 * Also part of the {@link GetTaxRequest}
 * result returned from the {@link TaxServiceSoap#getTax} tax calculation service;
 * No behavior - basically a glorified struct.
 *
 * <b>Example:</b>
 * <pre>
 *  $port = new AddressServiceSoap();
 *
 *  $address = new AvalaraAddress();
 *  $address->setLine1("900 Winslow Way");
 *  $address->setLine2("Suite 130");
 *  $address->setCity("Bainbridge Is");
 *  $address->setRegion("WA");
 *  $address->setPostalCode("98110-2450");
 *
 *  $result = $port->validate($address,TextCase::$Upper);
 *  $addresses = $result->ValidAddresses;
 *  print("Number of addresses returned is ". sizseof($addresses));
 *
 * </pre>
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvalaraAddress
 */
 
class AvalaraAddress
{

    private $AddressCode;
	private $Line1;
	private $Line2;
	private $Line3;
	private $City;
	private $Region;
	private $PostalCode;
    private $Country = 'USA';
    private $TaxRegionId = 0;

    /**
     * Construct a new Address.
     *
     * Constructs a new instance of Address. 
     * <pre>
     * $address = new AvalaraAddress();
     * </pre>
     *
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @param string $city
     * @param string $region
     * @param string $postalCode
     * @param string $country
     * @param integer $taxRegionId
     */

    public function __construct($line1=null,$line2=null,$line3=null,$city=null,$region=null,$postalCode=null,$country='USA',$taxRegionId=0)
    {
		$this->Line1 = $line1;
        $this->Line2 = $line2;
        $this->Line3 = $line3;
        $this->City = $city;
        $this->Region = $region;
        $this->PostalCode = $postalCode;
        $this->Country = $country;
        $this->TaxRegionId = $taxRegionId;
    }

	/**
	 * Programmatically determined value used internally by the adapter.
	 *
	 * @param string $value
	 * 
	 */
    public function setAddressCode($value) { $this->AddressCode = $value; }
    
    /**
     * Address line 1 
     *
     * @param string $value
     */
    public function setLine1($value) { $this->Line1 = $value; }
    
    /**
     * Address line 2
     *
     * @param string $value
     */
    public function setLine2($value) { $this->Line2 = $value; }
    
    /**
     * Address line 3 
     *
     * @param string $value
     */
    public function setLine3($value) { $this->Line3 = $value;  }
    
    /**
     * City name
     *
     * @param string $value
     */
    public function setCity($value) { $this->City = $value; }
    
    /**
     * State or province name or abbreviation
     *
     * @param string $value
     */
    public function setRegion($value) { $this->Region = $value; }
    
    /**
     * Postal or ZIP code
     *
     * @param string $value
     */
    public function setPostalCode($value) { $this->PostalCode = $value;  }
    
    /**
     * Country name
     *
     * @param string $value
     */
    public function setCountry($value) { $this->Country = $value; }


	/**
	 * TaxRegionId provides the ability to override the tax region assignment for an address. 
	 *
	 * @param string $value
	 */
    public function setTaxRegionId($value) { $this->TaxRegionId = $value;  }

 	/**
 	 * Programmatically determined value used internally by the adapter.
 	 *
 	 * @return string $value
 	 */
    public function getAddressCode() { return $this->AddressCode; }
    
    /**
     * Address line 1 
     *
     * @return string $value
     */
    public function getLine1() { return $this->Line1; }
    
   /**
     * Address line 2 
     *
     * @return string $value
     */
    public function getLine2() { return $this->Line2; }
    
    /**
     * Address line 3 
     *
     * @return string $value
     */
    public function getLine3() { return $this->Line3; }
    
    /**
     * City name 
     *
     * @return string $value
     */
    public function getCity() { return $this->City; }
    
    /**
     * State or province name or abbreviation
     *
     * @return string $value
     */
    public function getRegion() { return $this->Region; }
    
    /**
     * Postal or ZIP code 
     *
     * @return string $value
     */
    public function getPostalCode() { return $this->PostalCode; }
    
    /**
     * Country name
     *
     * @return string $value
     */
    public function getCountry() { return $this->AddressCode; }
    
    /**
     * TaxRegionId provides the ability to override the tax region assignment for an address. 
     *
     * @return string $value
     */
    public function getTaxRegionId() { return $this->TaxRegionId; }


	/**
	 * Compares Addresses
	 * @access public
	 * @param Address
	 * @return boolean
	 */
	public function equals(&$other)  // fix me after replace
	{
		return $this === $other || (
		strcmp($this->AddressCode , $other->AddressCode) == 0 &&
		strcmp($this->Line1 , $other->Line1) == 0 &&
		strcmp($this->Line2 , $other->Line2) == 0 &&
		strcmp($this->Line3 , $other->Line3) == 0 &&
		strcmp($this->City , $other->City) == 0 &&
		strcmp($this->Region , $other->Region) == 0 &&
		strcmp($this->PostalCode , $other->PostalCode) == 0 &&
		strcmp($this->Country , $other->Country) == 0 &&
		$this->TaxRegionId === $other->TaxRegionId
		);
	}
}

?>