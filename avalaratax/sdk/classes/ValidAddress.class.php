<?php
/**
 * ValidAddress.class.php
 */

/**
 * A fully validated address based on initial {@link Address}
 * data passed to {@link AddressServiceSoap#validate}.
 * <pre>
 * <b>Example:</b>
 *  $address = new Address();
 *  $address->setLine1("900 Winslow Way");
 *  $address->setLine2("Suite 130");
 *  $address->setCity("Bainbridge Is");
 *  $address->setRegion("WA");
 *  $address->setPostalCode("98110-2450");
 *
 *  $result = svc->validate($address,TextCase::$Upper);
 *
 *  if ($result->getResultCode() == SeverityLevel::$Success)
 *  {
 *      $addresses = result->validAddresses();
 *      if (sizeof($addresses) > 0)
 *      {
 *          $validAddress = $addresses[0];
 *          print($validAddress->getLine1()); // "900 WINSLOW WAY E STE 130",
 *          print($validAddress->getLine4()); // "BAINBRIDGE IS WA 98110-2450"
 *          print($validAddress->getFipsCode()); // "5303500000"
 *          print($validAddress->getCounty()); // "KITSAP"
 *      }
 *  }
 * </pre>
 *
 * @see Address
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */

class ValidAddress //extends Address - or it should - SoapClient has issues mapping attributes in superclasses
{
/**#@+
 * @access private
 * @var string
 */
    public $AddressCode;
	public $Line1;
	public $Line2;
	public $Line3;
	public $City;
	public $Region;
	public $PostalCode;
    public $Country = 'USA';

    public $Line4;
    public $County;
    public $FipsCode;
    public $CarrierRoute;
    public $PostNet;
    public $AddressType;
    public $Latitude;
    public $Longitude;
/**#@-*/

/**
 * @access private
 * @var integer
 */
    public $TaxRegionId = 0;


/**#@+
 * Accessor
 * @access public
 * @return string
 */
    public function getAddressCode() { return $this->AddressCode; }
    public function getLine1() { return $this->Line1; }
    public function getLine2() { return $this->Line2; }
    public function getLine3() { return $this->Line3; }
    public function getCity() { return $this->City; }
    public function getRegion() { return $this->Region; }
    public function getPostalCode() { return $this->PostalCode; }
    public function getCountry() { return $this->Country; }
/**#@-*/

/**
 * Accessor
 * @access public
 * @return integer
 */
    public function getTaxRegionId() { return $this->TaxRegionId; }

/**
 * Compare Addresses
 * @access public
 * @param Address
 * @return boolean
 */
 
	public function equals(&$other)
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


        
    /**
     * Address line 4.
     * 
     * @return line4 - Address line 4
     */

    public function getLine4() { return $this->Line4; }
	
    /**
     * County Name.
     * 
     * @return county - County Name
     */

    public function getCounty() { return $this->County; }
	
   /**
     * Federal Information Processing Standards Code (USA).
     * <p> This is a unique code representing each geographic combination of state, county, and city.
     * The code is made up of the Federal Information Processing Code (FIPS) that uniquely identifies each state, county, and city in the U.S.
     * See <a href="http://www.census.gov/geo/www/fips/fips.html">Federal Information Processing Standards (FIPS) Codes</a> for more details.
     * <table>
     * <tr>
     *     <th>Digits</th>
     *     <th>Description</th>
     * </tr>
     * <tr>
     *     <td>1-2</td>
     *     <td>State code</td>
     * </tr>
     * <tr>
     *     <td>3-5</td><td>County code</td>
     * </tr>
     * <tr>
     *     <td>6-10</td><td>City code</td>
     * </tr>
     *  </table>
     *
     * @return fipsCode
     */
	
    public function getFipsCode() { return $this->FipsCode; }
	
	    /**
     * The carrier route associated with the input address (USA).
     * <p>The CarrierRoute Property is a 4 character string set
     * after a successful return from the VerifyAddress Method.
     * <p>The first character of this property is always alphabetic,
     * and the last three characters are numeric. For example,
     * "R001" or "C027" would be typical carrier routes. The
     * alphabetic letter indicates the type of delivery associated
     * with this address.
     * <table>
     * <tr>
     * <th>Term</th>
     *
     * <th>Description</th>
     * </tr>
     * <tr>
     *     <td>B</td>
     *     <td>PO Box</td>
     * </tr>
     * <tr>
     *     <td>C</td>
     *     <td>City Delivery</td>
     * </tr>
     * <tr>
     *     <td>G</td>
     *     <td>General Delivery</td>
     * </tr>
     * <tr>
     *     <td>H</td>
     *     <td>Highway Contract</td>
     * </tr>
     * <tr>
     *     <td>R</td>
     *     <td>Rural Route</td>
     * </tr>
     * </table>
     *
     * @return carrierRoute
     */

    public function getCarrierRoute() { return $this->CarrierRoute; }

    /**
     * A 12-digit POSTNet barcode (USA).
     * <table>
     * <tr>
     *     <th>Digits</th>
     *     <th>Description</th>
     * </tr>
     * <tr>
     *     <td>1-5<td><td>ZIP Code</td>
     * </tr>
     * <tr>
     *     <td>6-9<td><td>Plus4 code</td>
     * </tr>
     * <tr>
     *     <td>10-11<td><td>Delivery point</td>
     * </tr>
     * <tr>
     *     <td>12<td><td>Check digit</td>
     * </tr>
     * </table>
     *
     * @return postNet
     */

    public function getPostNet() { return $this->PostNet; }
	
	    /**
     * Address Type - The type of address that was coded
     * (PO Box, Rural Route, and so on), using the input address.
     *
     * <table>
     * <tr>
     *     <th>Code</th>
     *     <th>Type</th>
     * </tr>
     * <tr>
     *     <td>F<td><td>Firm or company address</td>
     * </tr>
     * <tr>
     *     <td>G<td><td>General Delivery address</td>
     * </tr>
     * <tr>
     *     <td>H<td><td>High-rise or business complexs</td>
     * </tr>
     * <tr>
     *     <td>P<td><td>PO Box address</td>
     * </tr>
     * <tr>
     *     <td>R<td><td>Rural route address</td>
     * </tr>
     * <tr>
     *     <td>S<td><td>Street or residential address</td>
     * </tr>
     * </table>
	 *
     * @see AddressType
	 * @return string
	 */
	 
    public function getAddressType() { return $this->AddressType; }
	
    /**
     * Gets the latitude value for this ValidAddress.
     * 
     * @return latitude
     */

    public function getLatitude() { return $this->Latitude; }

    /**
     * Gets the longitude value for this ValidAddress.
     * 
     * @return longitude
     */
	 
    public function getLongitude() { return $this->Longitude; }

    // mutators
	
/**#@+
 * Mutator
 * @access public
 * @var string
 * @return Address
 */
    public function setAddressCode($value) { $this->AddressCode = $value; return $this; }
    public function setLine1($value) { $this->Line1 = $value; return $this; }
    public function setLine2($value) { $this->Line2 = $value; return $this; }
    public function setLine3($value) { $this->Line3 = $value; return $this; }
    public function setCity($value) { $this->City = $value; return $this; }
    public function setRegion($value) { $this->Region = $value; return $this; }
    public function setPostalCode($value) { $this->PostalCode = $value; return $this; }
    public function setCountry($value) { $this->Country = $value; return $this; }
/**#@-*/

/**
 * Mutator
 * @access public
 * @param integer
 * @return Address
 */
    public function setTaxRegionId($value) { $this->TaxRegionId = $value; return $this; }    
    /**
     * Address line 4.
     * 
     * @param line4 - Address line 4
     * @var string
     */

    public function setLine4($value) { $this->Line4 = $value; return $this; }
	
    /**
     * County Name.
     * 
     * @param county - County Name
     * @var string
     */

    public function setCounty($value) { $this->County= $value; return $this; }
	
    /**
     * Federal Information Processing Standards Code (USA).
     * <p> This is a unique code representing each geographic combination of state, county, and city.
     * The code is made up of the Federal Information Processing Code (FIPS) that uniquely identifies each state, county, and city in the U.S.
     * See <a href="http://www.census.gov/geo/www/fips/fips.html">Federal Information Processing Standards (FIPS) Codes</a> for more details.
     * <table>
     * <tr>
     *     <th>Digits</th>
     *     <th>Description</th>
     * </tr>
     * <tr>
     *     <td>1-2</td>
     *     <td>State code</td>
     * </tr>
     * <tr>
     *     <td>3-5</td><td>County code</td>
     * </tr>
     * <tr>
     *     <td>6-10</td><td>City code</td>
     * </tr>
     *  </table>
     *
     * @param fipsCode
     * @var string
     */
 
    public function setFipsCode($value) { $this->FipsCode= $value; return $this; }
	
	/**
     * The carrier route associated with the input address (USA).
     * <p>The CarrierRoute Property is a 4 character string set
     * after a successful return from the VerifyAddress Method.
     * <p>The first character of this property is always alphabetic,
     * and the last three characters are numeric. For example,
     * "R001" or "C027" would be typical carrier routes. The
     * alphabetic letter indicates the type of delivery associated
     * with this address.
     * <table>
     * <tr>
     * <th>Term</th>
     *
     * <th>Description</th>
     * </tr>
     * <tr>
     *     <td>B</td>
     *     <td>PO Box</td>
     * </tr>
     * <tr>
     *     <td>C</td>
     *     <td>City Delivery</td>
     * </tr>
     * <tr>
     *     <td>G</td>
     *     <td>General Delivery</td>
     * </tr>
     * <tr>
     *     <td>H</td>
     *     <td>Highway Contract</td>
     * </tr>
     * <tr>
     *     <td>R</td>
     *     <td>Rural Route</td>
     * </tr>
     * </table>
     *
     * @param carrierRoute
     * @var string
     */

    public function setCarrierRoute($value) { $this->CarrierRoute= $value; return $this; }

    /**
     * A 12-digit POSTNet barcode (USA).
     * <table>
     * <tr>
     *     <th>Digits</th>
     *     <th>Description</th>
     * </tr>
     * <tr>
     *     <td>1-5<td><td>ZIP Code</td>
     * </tr>
     * <tr>
     *     <td>6-9<td><td>Plus4 code</td>
     * </tr>
     * <tr>
     *     <td>10-11<td><td>Delivery point</td>
     * </tr>
     * <tr>
     *     <td>12<td><td>Check digit</td>
     * </tr>
     * </table>
     *
     * @param postNet
     * @var string
     */

    public function setPostNet($value) { $this->PostNet= $value; return $this; }
	
    /**
     * Address Type - The type of address that was coded
     * (PO Box, Rural Route, and so on), using the input address.
     *
     * <table>
     * <tr>
     *     <th>Code</th>
     *     <th>Type</th>
     * </tr>
     * <tr>
     *     <td>F<td><td>Firm or company address</td>
     * </tr>
     * <tr>
     *     <td>G<td><td>General Delivery address</td>
     * </tr>
     * <tr>
     *     <td>H<td><td>High-rise or business complexs</td>
     * </tr>
     * <tr>
     *     <td>P<td><td>PO Box address</td>
     * </tr>
     * <tr>
     *     <td>R<td><td>Rural route address</td>
     * </tr>
     * <tr>
     *     <td>S<td><td>Street or residential address</td>
     * </tr>
     * </table>
	 *
     * @see AddressType
	 * @param addressType
     * @var string
	 */
	 
    public function setAddressType($value) { $this->AddressType= $value; return $this; }
	
    /**
     * Sets the latitude value for this ValidAddress.
     * 
     * @param latitude
     */

    public function setLatitude($value) { $this->Latitude= $value; return $this;}

    /**
     * Sets the longitude value for this ValidAddress.
     * 
     * @param longitude
     */

    public function setLongitude($value) { $this->Longitude= $value; return $this; }
    
    
    
}

?>