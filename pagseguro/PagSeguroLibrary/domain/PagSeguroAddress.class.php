<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }
/*
************************************************************************
Copyright [2011] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Represents an address location, typically for shipping or charging purposes. 
 * @see PagSeguroShipping
 */
class PagSeguroAddress {
	
	
	private $postalCode;

	/**
	 * Street name
	 */
	private $street;

	/**
	 * Number
	 */
	private $number;

	/**
	 * Apartment, suite number or any other qualifier after the street/number pair.
	 * Example: Apt 274, building A.
	 */
	private $complement;

	/**
	 * District, county or neighborhood, if applicable
	 */
	private $district;

	/**
	 * City
	 */
	private $city;

	/**
	 * State or province
	 */
	private $state;

	/**
	 * Country
	 */
	private $country;

	/**
	 * Initializes a new instance of the Address class
	 * @param array $data
	 */
	public function __construct(Array $data = null) {
		if (isset($data['postalCode'])) {
			$this->postalCode = $data['postalCode'];
		}
		if (isset($data['street'])) {
			$this->street = $data['street'];
		}
		if (isset($data['number'])) {
			$this->number = $data['number'];
		}
		if (isset($data['complement'])) {
			$this->complement = $data['complement'];
		}
		if (isset($data['district'])) {
			$this->district = $data['district'];
		}
		if (isset($data['city'])) {
			$this->city = $data['city'];
		}
		if (isset($data['state'])) {
			$this->state = $data['state'];
		}
		if (isset($data['country'])) {
			$this->country = $data['country'];
		}
	}

	/**
	 * @return the street
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * @return the number
	 */
	public function getNumber() {
		return $this->number;
	}
	
	/**
	 * @return the complement
	 */
	public function getComplement() {
		return $this->complement;
	}

	/**
	 * @return the distrcit
	 */
	public function getDistrict() {
		return $this->district;
	}
	
	/**
	 * @return the city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @return the state
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @return the postal code
	 */
	public function getPostalCode() {
		return $this->postalCode;
	}

	/**
	 * @return the country
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Sets the country
	 * @param String $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Sets the street
	 * @param String $street
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * sets the numbetr
	 * @param String $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * Sets the complement
	 * @param String $complement
	 */
	public function setComplement($complement) {
		$this->complement = $complement;
	}

	/**
	 * sets the district
	 * @param String $district
	 */
	public function setDistrict($district) {
		$this->district = $district;
	}

	/**
	 * Sets the city
	 * @param String $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Sets the state 
	 * @param String $state
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Sets the postal code
	 * @param String $postalCode
	 */
	public function setPostalCode($postalCode) {
		$this->postalCode = $postalCode;
	}
	
}

?>