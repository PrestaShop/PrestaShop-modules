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
 * Represents a phone number
 */	
class PagSeguroPhone {
	
	/**
	 * Area code
	 */
	private $areaCode;

	/**
	 * Phone number
	 */
	private $number;
	
	/**
	 * Initializes a new instance of the PagSeguroPhone class
	 * 
	 * @param String $areaCode
	 * @param String $number
	 */
	public function __construct($areaCode = null, $number = null) {
		$this->areaCode = ($areaCode == null ? null : $areaCode);
		$this->number   = ($number   == null ? null : $number);
	}
	
	/**
	 * @return the area code
	 */
	public function getAreaCode() {
		return $this->areaCode;
	}
	
	/**
	 * @return the number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * Sets the area code
	 * @param String $areaCode
	 */
	public function setAreaCode($areaCode) {
		$this->areaCode = $areaCode;
	}

	/**
	 * Sets the number
	 * @param String $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}
	
}
	
?>